"""
Investor, Rincian Invest — KepalaToko only
"""
from fastapi import APIRouter, Depends, Query
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func, and_, update
from typing import Optional
from datetime import datetime

from app.core.database import get_db
from app.core.security import require_kepala_toko
from app.models.models import RincianInvest
from app.utils.query import paginate

router = APIRouter()


@router.get("/rincian-invest")
async def list_rincian_invest(
    page: int = Query(1), per_page: int = Query(15),
    db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko),
):
    base = RincianInvest.cabang_id == cu.cabang_id
    q = select(
        RincianInvest.id, RincianInvest.nama, RincianInvest.nominal,
        RincianInvest.persentase, RincianInvest.keterangan, RincianInvest.created_at,
    ).where(base).order_by(RincianInvest.created_at.desc())
    cq = select(func.count(RincianInvest.id)).where(base)
    return await paginate(db, q, cq, page, per_page)


@router.post("/rincian-invest", status_code=201)
async def create_rincian_invest(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    ri = RincianInvest(
        cabang_id=cu.cabang_id,
        nama=body["nama"],
        nominal=body.get("nominal", 0),
        persentase=body.get("persentase", 0),
        keterangan=body.get("keterangan"),
    )
    db.add(ri)
    await db.flush()
    return {"id": ri.id}


@router.put("/rincian-invest/{id}")
async def update_rincian_invest(id: int, body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    await db.execute(
        update(RincianInvest).where(RincianInvest.id == id, RincianInvest.cabang_id == cu.cabang_id)
        .values(nama=body.get("nama"), nominal=body.get("nominal"), persentase=body.get("persentase"),
                keterangan=body.get("keterangan"), updated_at=datetime.utcnow())
    )
    return {"message": "Rincian invest diperbarui"}


@router.delete("/rincian-invest/{id}")
async def delete_rincian_invest(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    from sqlalchemy import delete as sd
    await db.execute(sd(RincianInvest).where(RincianInvest.id == id, RincianInvest.cabang_id == cu.cabang_id))
    return {"message": "Dihapus"}


@router.post("/rincian-invest/bulk-delete")
async def bulk_delete_rincian(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    from sqlalchemy import delete as sd
    await db.execute(sd(RincianInvest).where(RincianInvest.id.in_(body.get("ids", [])), RincianInvest.cabang_id == cu.cabang_id))
    return {"message": "Berhasil dihapus"}


@router.get("/investor/pembagian-hasil")
async def pembagian_hasil(
    bulan: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko),
):
    """
    Hitung pembagian hasil investasi berdasarkan profit bulan ini
    dan persentase tiap investor.
    """
    from sqlalchemy import extract
    from app.models.models import TransaksiServis, Order

    now = datetime.now()
    if bulan:
        dt = datetime.strptime(bulan, "%Y-%m")
        bln, thn = dt.month, dt.year
    else:
        bln, thn = now.month, now.year

    # Profit servis bulan ini
    r_s = await db.execute(
        select(func.sum(TransaksiServis.profit).label("profit_servis")).where(
            TransaksiServis.cabang_id == cu.cabang_id,
            TransaksiServis.deleted_at.is_(None),
            extract("month", TransaksiServis.tgl_selesai) == bln,
            extract("year", TransaksiServis.tgl_selesai) == thn,
        )
    )
    profit_servis = float(r_s.scalar_one_or_none() or 0)

    # Profit penjualan bulan ini
    r_o = await db.execute(
        select(func.sum(Order.total - Order.diskon).label("profit_penjualan")).where(
            Order.cabang_id == cu.cabang_id,
            Order.deleted_at.is_(None),
            extract("month", Order.created_at) == bln,
            extract("year", Order.created_at) == thn,
        )
    )
    profit_penjualan = float(r_o.scalar_one_or_none() or 0)
    total_profit = profit_servis + profit_penjualan

    # Investors
    r_inv = await db.execute(select(RincianInvest).where(RincianInvest.cabang_id == cu.cabang_id))
    investors = r_inv.scalars().all()

    hasil = []
    for inv in investors:
        bagian = total_profit * float(inv.persentase or 0) / 100
        hasil.append({
            "nama": inv.nama,
            "persentase": float(inv.persentase or 0),
            "nominal_dapat": bagian,
        })

    return {
        "bulan": f"{bln:02d}/{thn}",
        "profit_servis": profit_servis,
        "profit_penjualan": profit_penjualan,
        "total_profit": total_profit,
        "pembagian": hasil,
    }
