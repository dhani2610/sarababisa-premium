"""
Dashboard API — optimized aggregate queries
Semua count dan sum pakai SUM/COUNT di DB, bukan load ke Python
"""
from fastapi import APIRouter, Depends, Query
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func, text, and_
from datetime import date, datetime

from app.core.database import get_db
from app.core.security import get_current_user, require_any
from app.core.cache import cache_get, cache_set, CacheTTL, CacheKeys
from app.models.models import (
    TransaksiServis, Order, Pengeluaran, Produk, User
)

router = APIRouter()


@router.get("/dashboard")
async def dashboard(
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    cabang_id = current_user.cabang_id
    cache_key = CacheKeys.dashboard(cabang_id, "main")

    cached = await cache_get(cache_key)
    if cached:
        return cached

    today = date.today()
    bulan = today.month
    tahun = today.year

    # ── Servis stats — 1 query dengan conditional aggregation ──
    servis_stats = await db.execute(
        select(
            func.count(TransaksiServis.id).label("total"),
            func.sum(
                func.IF(TransaksiServis.status == "proses", 1, 0)
            ).label("proses"),
            func.sum(
                func.IF(TransaksiServis.status == "bisa_diambil", 1, 0)
            ).label("bisa_diambil"),
            func.sum(
                func.IF(TransaksiServis.status == "sudah_diambil", 1, 0)
            ).label("sudah_diambil"),
            func.sum(
                func.IF(
                    and_(
                        TransaksiServis.status == "sudah_diambil",
                        func.month(TransaksiServis.created_at) == bulan,
                        func.year(TransaksiServis.created_at) == tahun,
                    ),
                    TransaksiServis.total_biaya, 0
                )
            ).label("omzet_servis_bulan"),
            func.sum(
                func.IF(
                    and_(
                        TransaksiServis.status == "sudah_diambil",
                        func.date(TransaksiServis.created_at) == today,
                    ),
                    TransaksiServis.total_biaya, 0
                )
            ).label("omzet_servis_hari"),
        )
        .where(
            TransaksiServis.cabang_id == cabang_id,
            TransaksiServis.deleted_at.is_(None),
        )
    )
    servis = servis_stats.first()

    # ── Penjualan stats ──
    penjualan_stats = await db.execute(
        select(
            func.count(Order.id).label("total"),
            func.sum(
                func.IF(
                    and_(
                        Order.status == "lunas",
                        func.month(Order.created_at) == bulan,
                        func.year(Order.created_at) == tahun,
                    ),
                    Order.total, 0
                )
            ).label("omzet_penjualan_bulan"),
            func.sum(
                func.IF(
                    and_(
                        Order.status == "lunas",
                        func.date(Order.created_at) == today,
                    ),
                    Order.total, 0
                )
            ).label("omzet_penjualan_hari"),
        )
        .where(
            Order.cabang_id == cabang_id,
            Order.deleted_at.is_(None),
        )
    )
    penjualan = penjualan_stats.first()

    # ── Pengeluaran bulan ini ──
    pengeluaran_stats = await db.execute(
        select(
            func.sum(Pengeluaran.jumlah).label("total_pengeluaran_bulan")
        )
        .where(
            Pengeluaran.cabang_id == cabang_id,
            Pengeluaran.deleted_at.is_(None),
            func.month(Pengeluaran.tgl_pengeluaran) == bulan,
            func.year(Pengeluaran.tgl_pengeluaran) == tahun,
        )
    )
    pengeluaran = pengeluaran_stats.first()

    # ── Stok alert ──
    stok_habis = await db.execute(
        select(func.count(Produk.id))
        .where(
            Produk.cabang_id == cabang_id,
            Produk.deleted_at.is_(None),
            Produk.stok <= 0,
        )
    )
    stok_menipis = await db.execute(
        select(func.count(Produk.id))
        .where(
            Produk.cabang_id == cabang_id,
            Produk.deleted_at.is_(None),
            Produk.stok > 0,
            Produk.stok <= Produk.stok_minimum,
        )
    )

    omzet_bulan = float(servis.omzet_servis_bulan or 0) + float(penjualan.omzet_penjualan_bulan or 0)
    omzet_hari = float(servis.omzet_servis_hari or 0) + float(penjualan.omzet_penjualan_hari or 0)
    total_pengeluaran = float(pengeluaran.total_pengeluaran_bulan or 0)
    profit_bulan = omzet_bulan - total_pengeluaran

    result = {
        "servis": {
            "total": servis.total or 0,
            "proses": int(servis.proses or 0),
            "bisa_diambil": int(servis.bisa_diambil or 0),
            "sudah_diambil": int(servis.sudah_diambil or 0),
            "omzet_hari_ini": omzet_hari,
            "omzet_bulan_ini": float(servis.omzet_servis_bulan or 0),
        },
        "penjualan": {
            "total": penjualan.total or 0,
            "omzet_hari_ini": float(penjualan.omzet_penjualan_hari or 0),
            "omzet_bulan_ini": float(penjualan.omzet_penjualan_bulan or 0),
        },
        "keuangan": {
            "omzet_total_bulan": omzet_bulan,
            "pengeluaran_bulan": total_pengeluaran,
            "profit_bulan": profit_bulan,
        },
        "produk": {
            "stok_habis": stok_habis.scalar() or 0,
            "stok_menipis": stok_menipis.scalar() or 0,
        },
    }

    await cache_set(cache_key, result, CacheTTL.SHORT)
    return result


@router.get("/dashboard/grafik-cabang")
async def dashboard_grafik_cabang(
    bulan: Optional[str] = Query(None, description="YYYY-MM"),
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    from app.models.models import Cabang
    now = datetime.now()
    if bulan:
        try:
            dt = datetime.strptime(bulan, "%Y-%m")
            bln, thn = dt.month, dt.year
        except ValueError:
            bln, thn = now.month, now.year
    else:
        bln, thn = now.month, now.year

    r_cabang = await db.execute(select(Cabang.id, Cabang.nama).where(Cabang.deleted_at.is_(None)).order_by(Cabang.id))
    cabangs = r_cabang.mappings().all()

    comparison = []
    for c in cabangs:
        cid = c["id"]
        # Omzet servis
        q_s = await db.execute(
            select(func.sum(TransaksiServis.total_biaya), func.count(TransaksiServis.id))
            .where(
                TransaksiServis.cabang_id == cid,
                TransaksiServis.deleted_at.is_(None),
                func.month(TransaksiServis.created_at) == bln,
                func.year(TransaksiServis.created_at) == thn,
            )
        )
        s_row = q_s.first()
        omzet_s = float(s_row[0] or 0)
        unit_s = int(s_row[1] or 0)

        # Omzet penjualan
        q_p = await db.execute(
            select(func.sum(Order.total), func.count(Order.id))
            .where(
                Order.cabang_id == cid,
                Order.deleted_at.is_(None),
                Order.status == "lunas",
                func.month(Order.created_at) == bln,
                func.year(Order.created_at) == thn,
            )
        )
        p_row = q_p.first()
        omzet_p = float(p_row[0] or 0)
        order_p = int(p_row[1] or 0)

        # Pengeluaran
        q_e = await db.execute(
            select(func.sum(Pengeluaran.jumlah))
            .where(
                Pengeluaran.cabang_id == cid,
                Pengeluaran.deleted_at.is_(None),
                func.month(Pengeluaran.tgl_pengeluaran) == bln,
                func.year(Pengeluaran.tgl_pengeluaran) == thn,
            )
        )
        pengeluaran = float(q_e.scalar() or 0)

        total_omzet = omzet_s + omzet_p
        net_profit = total_omzet - pengeluaran

        comparison.append({
            "cabang_id": cid,
            "cabang_nama": c["nama"],
            "omzet_servis": omzet_s,
            "unit_servis": unit_s,
            "omzet_penjualan": omzet_p,
            "total_penjualan": order_p,
            "total_omzet": total_omzet,
            "pengeluaran": pengeluaran,
            "profit": net_profit,
        })

    return {
        "bulan": f"{thn}-{bln:02d}",
        "data": comparison,
    }

