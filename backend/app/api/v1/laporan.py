"""
Laporan API — servis, penjualan, teknisi, sales, admin, pajak, cetak PDF
Semua query dioptimasi untuk data besar: index-first, no N+1, streaming PDF.
"""
from fastapi import APIRouter, Depends, Query, Response
from fastapi.responses import StreamingResponse
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func, and_, or_
from typing import Optional
from datetime import date, datetime
import io

from app.core.database import get_db
from app.core.security import require_any, require_kepala_toko, require_admin_toko
from app.models.models import (
    TransaksiServis, Order, OrderDetail, User,
    Pelanggan, Merek, ModelSeri, StoreSetting, SyaratKetentuan
)

router = APIRouter()


def _bulan_filter(model, col, bulan: Optional[str]):
    """Helper filter bulan: '2024-09' → filter year+month."""
    if not bulan:
        return []
    try:
        dt = datetime.strptime(bulan, "%Y-%m")
        return [
            func.year(col) == dt.year,
            func.month(col) == dt.month,
        ]
    except ValueError:
        return []


# ─── Laporan Servis ───────────────────────────────────────
@router.get("/laporan/servis")
async def laporan_servis(
    bulan: Optional[str] = Query(None, description="Format: YYYY-MM"),
    teknisi_id: Optional[int] = None,
    status: Optional[str] = None,
    date_from: Optional[date] = None,
    date_to: Optional[date] = None,
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_any),
):
    """Laporan servis per bulan dengan summary."""
    cabang_id = cu.cabang_id
    filters = [
        TransaksiServis.cabang_id == cabang_id,
        TransaksiServis.deleted_at.is_(None),
    ]
    filters += _bulan_filter(TransaksiServis, TransaksiServis.created_at, bulan)

    if status:
        filters.append(TransaksiServis.status == status)
    if teknisi_id:
        filters.append(TransaksiServis.teknisi_id == teknisi_id)
    if date_from:
        filters.append(TransaksiServis.tgl_masuk >= date_from)
    if date_to:
        filters.append(TransaksiServis.tgl_masuk <= date_to)

    # Data list
    q = (
        select(
            TransaksiServis.id, TransaksiServis.no_nota, TransaksiServis.status,
            TransaksiServis.total_biaya, TransaksiServis.dp, TransaksiServis.sisa_bayar,
            TransaksiServis.biaya_tindakan, TransaksiServis.biaya_sparepart,
            TransaksiServis.diskon, TransaksiServis.tgl_masuk, TransaksiServis.tgl_selesai,
            TransaksiServis.profit, TransaksiServis.is_approved, TransaksiServis.created_at,
            Pelanggan.nama.label("pelanggan_nama"),
            Merek.nama.label("merek_nama"),
            ModelSeri.nama.label("model_seri_nama"),
            User.nama.label("teknisi_nama"),
        )
        .outerjoin(Pelanggan, Pelanggan.id == TransaksiServis.pelanggan_id)
        .outerjoin(Merek, Merek.id == TransaksiServis.merek_id)
        .outerjoin(ModelSeri, ModelSeri.id == TransaksiServis.model_seri_id)
        .outerjoin(User, User.id == TransaksiServis.teknisi_id)
        .where(and_(*filters))
        .order_by(TransaksiServis.created_at.desc())
    )

    # Summary aggregate
    sq = (
        select(
            func.count(TransaksiServis.id).label("total_transaksi"),
            func.sum(TransaksiServis.total_biaya).label("total_pendapatan"),
            func.sum(TransaksiServis.profit).label("total_profit"),
            func.sum(TransaksiServis.sisa_bayar).label("total_piutang"),
            func.sum(TransaksiServis.biaya_sparepart).label("total_sparepart"),
        )
        .where(and_(*filters))
    )

    res_data, res_sum = await db.execute(q), await db.execute(sq)
    rows = res_data.mappings().all()
    summary = res_sum.mappings().first()

    return {
        "data": rows,
        "summary": {
            "total_transaksi": summary["total_transaksi"] or 0,
            "total_pendapatan": float(summary["total_pendapatan"] or 0),
            "total_profit": float(summary["total_profit"] or 0),
            "total_piutang": float(summary["total_piutang"] or 0),
            "total_sparepart": float(summary["total_sparepart"] or 0),
        },
    }


# ─── Laporan Penjualan ────────────────────────────────────
@router.get("/laporan/penjualan")
async def laporan_penjualan(
    bulan: Optional[str] = Query(None),
    date_from: Optional[date] = None,
    date_to: Optional[date] = None,
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_any),
):
    """Laporan transaksi produk/POS per bulan."""
    filters = [
        Order.cabang_id == cu.cabang_id,
        Order.deleted_at.is_(None),
    ]
    filters += _bulan_filter(Order, Order.created_at, bulan)
    if date_from:
        filters.append(Order.created_at >= date_from)
    if date_to:
        filters.append(Order.created_at <= date_to)

    q = (
        select(
            Order.id, Order.no_invoice, Order.status,
            Order.total, Order.diskon, Order.ppn, Order.dp, Order.sisa_bayar,
            Order.created_at,
            Pelanggan.nama.label("pelanggan_nama"),
            User.nama.label("kasir_nama"),
        )
        .outerjoin(Pelanggan, Pelanggan.id == Order.pelanggan_id)
        .outerjoin(User, User.id == Order.user_id)
        .where(and_(*filters))
        .order_by(Order.created_at.desc())
    )

    sq = (
        select(
            func.count(Order.id).label("total_transaksi"),
            func.sum(Order.total).label("total_pendapatan"),
            func.sum(Order.sisa_bayar).label("total_piutang"),
            func.sum(Order.diskon).label("total_diskon"),
        )
        .where(and_(*filters))
    )

    res_data, res_sum = await db.execute(q), await db.execute(sq)
    summary = res_sum.mappings().first()

    return {
        "data": res_data.mappings().all(),
        "summary": {
            "total_transaksi": summary["total_transaksi"] or 0,
            "total_pendapatan": float(summary["total_pendapatan"] or 0),
            "total_piutang": float(summary["total_piutang"] or 0),
            "total_diskon": float(summary["total_diskon"] or 0),
        },
    }


# ─── Laporan Teknisi ──────────────────────────────────────
@router.get("/laporan/teknisi")
async def laporan_teknisi(
    bulan: Optional[str] = Query(None),
    teknisi_id: Optional[int] = None,
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_any),
):
    """Rekapitulasi kinerja teknisi per bulan."""
    filters = [
        TransaksiServis.cabang_id == cu.cabang_id,
        TransaksiServis.deleted_at.is_(None),
        TransaksiServis.status == "sudah_diambil",
    ]
    filters += _bulan_filter(TransaksiServis, TransaksiServis.tgl_selesai, bulan)
    if teknisi_id:
        filters.append(TransaksiServis.teknisi_id == teknisi_id)

    q = (
        select(
            User.id.label("teknisi_id"),
            User.nama.label("teknisi_nama"),
            func.count(TransaksiServis.id).label("jumlah_servis"),
            func.sum(TransaksiServis.total_biaya).label("total_pendapatan"),
            func.sum(TransaksiServis.profit).label("total_profit"),
            func.sum(TransaksiServis.biaya_tindakan).label("total_tindakan"),
        )
        .join(User, User.id == TransaksiServis.teknisi_id)
        .where(and_(*filters))
        .group_by(User.id, User.nama)
        .order_by(func.count(TransaksiServis.id).desc())
    )
    r = await db.execute(q)
    return r.mappings().all()


# ─── Laporan Admin/Sales ───────────────────────────────────
@router.get("/laporan/admin")
async def laporan_admin(
    bulan: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_kepala_toko),
):
    """Rekapitulasi kinerja admin per bulan (dari servis dan penjualan)."""
    filters_s = [
        TransaksiServis.cabang_id == cu.cabang_id,
        TransaksiServis.deleted_at.is_(None),
    ]
    filters_s += _bulan_filter(TransaksiServis, TransaksiServis.created_at, bulan)

    q = (
        select(
            User.id, User.nama, User.role,
            func.count(TransaksiServis.id).label("total_servis"),
            func.sum(TransaksiServis.total_biaya).label("total_pendapatan_servis"),
        )
        .join(User, User.id == TransaksiServis.user_id)
        .where(and_(*filters_s))
        .group_by(User.id, User.nama, User.role)
    )
    r = await db.execute(q)
    return r.mappings().all()


@router.get("/laporan/sales")
async def laporan_sales(
    bulan: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_kepala_toko),
):
    """Rekapitulasi kinerja sales per bulan."""
    filters = [Order.cabang_id == cu.cabang_id, Order.deleted_at.is_(None)]
    filters += _bulan_filter(Order, Order.created_at, bulan)

    q = (
        select(
            User.id, User.nama,
            func.count(Order.id).label("total_transaksi"),
            func.sum(Order.total).label("total_penjualan"),
            func.sum(Order.diskon).label("total_diskon"),
        )
        .join(User, User.id == Order.user_id)
        .where(and_(*filters))
        .group_by(User.id, User.nama)
        .order_by(func.sum(Order.total).desc())
    )
    r = await db.execute(q)
    return r.mappings().all()


# ─── Laporan Pajak ─────────────────────────────────────────
@router.get("/laporan/servis/pajak")
async def laporan_servis_pajak(
    bulan: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_kepala_toko),
):
    """Laporan PPN servis per bulan."""
    filters = [
        TransaksiServis.cabang_id == cu.cabang_id,
        TransaksiServis.deleted_at.is_(None),
    ]
    filters += _bulan_filter(TransaksiServis, TransaksiServis.created_at, bulan)

    q = select(
        TransaksiServis.no_nota,
        TransaksiServis.total_biaya,
        TransaksiServis.ppn,
        TransaksiServis.created_at,
        Pelanggan.nama.label("pelanggan"),
    ).outerjoin(Pelanggan, Pelanggan.id == TransaksiServis.pelanggan_id).where(and_(*filters))
    r = await db.execute(q)
    data = r.mappings().all()
    total_ppn = sum(float(d["ppn"] or 0) for d in data)
    return {"data": data, "total_ppn": total_ppn}


@router.get("/laporan/penjualan/pajak")
async def laporan_penjualan_pajak(
    bulan: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_kepala_toko),
):
    """Laporan PPN penjualan per bulan."""
    filters = [Order.cabang_id == cu.cabang_id, Order.deleted_at.is_(None)]
    filters += _bulan_filter(Order, Order.created_at, bulan)

    q = select(
        Order.no_invoice, Order.total, Order.ppn, Order.created_at,
        Pelanggan.nama.label("pelanggan"),
    ).outerjoin(Pelanggan, Pelanggan.id == Order.pelanggan_id).where(and_(*filters))
    r = await db.execute(q)
    data = r.mappings().all()
    total_ppn = sum(float(d["ppn"] or 0) for d in data)
    return {"data": data, "total_ppn": total_ppn}


# ─── PDF Cetak Laporan ─────────────────────────────────────
@router.get("/laporan/servis/cetak")
async def cetak_laporan_servis(
    bulan: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_any),
):
    """Generate PDF laporan servis — menggunakan reportlab."""
    try:
        from reportlab.lib.pagesizes import A4
        from reportlab.platypus import SimpleDocTemplate, Table, TableStyle, Paragraph, Spacer
        from reportlab.lib import colors
        from reportlab.lib.styles import getSampleStyleSheet

        data_resp = await laporan_servis(bulan=bulan, db=db, cu=cu)
        rows_data = data_resp["data"]
        summary = data_resp["summary"]

        buf = io.BytesIO()
        doc = SimpleDocTemplate(buf, pagesize=A4, leftMargin=36, rightMargin=36, topMargin=36, bottomMargin=36)
        styles = getSampleStyleSheet()
        story = []

        # Header
        story.append(Paragraph(f"<b>LAPORAN SERVIS</b>", styles["Title"]))
        story.append(Paragraph(f"Bulan: {bulan or 'Semua'} | Cabang ID: {cu.cabang_id}", styles["Normal"]))
        story.append(Spacer(1, 12))

        # Table
        headers = ["No", "No Nota", "Pelanggan", "Teknisi", "Status", "Total Biaya", "Profit"]
        table_data = [headers]
        for i, row in enumerate(rows_data, 1):
            table_data.append([
                str(i),
                str(row.get("no_nota", "")),
                str(row.get("pelanggan_nama", "-")),
                str(row.get("teknisi_nama", "-")),
                str(row.get("status", "")),
                f"Rp {float(row.get('total_biaya', 0)):,.0f}",
                f"Rp {float(row.get('profit', 0) or 0):,.0f}",
            ])

        # Summary row
        table_data.append([
            "", "TOTAL", "", "", "",
            f"Rp {summary['total_pendapatan']:,.0f}",
            f"Rp {summary['total_profit']:,.0f}",
        ])

        t = Table(table_data, repeatRows=1)
        t.setStyle(TableStyle([
            ("BACKGROUND", (0, 0), (-1, 0), colors.darkblue),
            ("TEXTCOLOR", (0, 0), (-1, 0), colors.white),
            ("FONTNAME", (0, 0), (-1, 0), "Helvetica-Bold"),
            ("FONTSIZE", (0, 0), (-1, -1), 8),
            ("GRID", (0, 0), (-1, -1), 0.5, colors.grey),
            ("BACKGROUND", (0, -1), (-1, -1), colors.lightgrey),
            ("FONTNAME", (0, -1), (-1, -1), "Helvetica-Bold"),
        ]))
        story.append(t)

        doc.build(story)
        buf.seek(0)
        return StreamingResponse(buf, media_type="application/pdf",
                                  headers={"Content-Disposition": f"attachment; filename=laporan_servis_{bulan or 'all'}.pdf"})
    except ImportError:
        return {"error": "reportlab tidak terinstall. Jalankan: pip install reportlab"}


@router.get("/laporan/penjualan/cetak")
async def cetak_laporan_penjualan(
    bulan: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_any),
):
    """Generate PDF laporan penjualan/POS — menggunakan reportlab."""
    try:
        from reportlab.lib.pagesizes import A4
        from reportlab.platypus import SimpleDocTemplate, Table, TableStyle, Paragraph, Spacer
        from reportlab.lib import colors
        from reportlab.lib.styles import getSampleStyleSheet

        data_resp = await laporan_penjualan(bulan=bulan, db=db, cu=cu)
        rows_data = data_resp["data"]
        summary = data_resp["summary"]

        buf = io.BytesIO()
        doc = SimpleDocTemplate(buf, pagesize=A4, leftMargin=36, rightMargin=36, topMargin=36, bottomMargin=36)
        styles = getSampleStyleSheet()
        story = []

        # Header
        story.append(Paragraph(f"<b>LAPORAN PENJUALAN / POS</b>", styles["Title"]))
        story.append(Paragraph(f"Bulan: {bulan or 'Semua'} | Cabang ID: {cu.cabang_id}", styles["Normal"]))
        story.append(Spacer(1, 12))

        # Table
        headers = ["No", "No Invoice", "Pelanggan", "Kasir", "Status", "Diskon", "Total"]
        table_data = [headers]
        for i, row in enumerate(rows_data, 1):
            table_data.append([
                str(i),
                str(row.get("no_invoice", "")),
                str(row.get("pelanggan_nama", "-")),
                str(row.get("kasir_nama", "-")),
                str(row.get("status", "")),
                f"Rp {float(row.get('diskon', 0) or 0):,.0f}",
                f"Rp {float(row.get('total', 0) or 0):,.0f}",
            ])

        # Summary row
        table_data.append([
            "", "TOTAL", "", "", "",
            f"Rp {summary['total_diskon']:,.0f}",
            f"Rp {summary['total_pendapatan']:,.0f}",
        ])

        t = Table(table_data, repeatRows=1)
        t.setStyle(TableStyle([
            ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#1e293b")),
            ("TEXTCOLOR", (0, 0), (-1, 0), colors.white),
            ("FONTNAME", (0, 0), (-1, 0), "Helvetica-Bold"),
            ("FONTSIZE", (0, 0), (-1, -1), 8),
            ("GRID", (0, 0), (-1, -1), 0.5, colors.grey),
            ("BACKGROUND", (0, -1), (-1, -1), colors.lightgrey),
            ("FONTNAME", (0, -1), (-1, -1), "Helvetica-Bold"),
        ]))
        story.append(t)

        doc.build(story)
        buf.seek(0)
        return StreamingResponse(buf, media_type="application/pdf",
                                  headers={"Content-Disposition": f"attachment; filename=laporan_penjualan_{bulan or 'all'}.pdf"})
    except ImportError:
        return {"error": "reportlab tidak terinstall. Jalankan: pip install reportlab"}

