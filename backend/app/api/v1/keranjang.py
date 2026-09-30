"""
Keranjang (Recycle Bin / Trash) API — restore atau permanent delete soft-deleted records.
Multi-cabang: restore hanya boleh di cabang yang sama.
"""
from fastapi import APIRouter, Depends, Query, HTTPException
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func, and_, update
from typing import Optional
from datetime import datetime

from app.core.database import get_db
from app.core.security import require_kepala_toko
from app.models.models import (
    TransaksiServis, Order, Pelanggan, Produk, User,
    Pengeluaran, Kasbon, Insiden
)

router = APIRouter()

# Map type → model
MODEL_MAP = {
    "servis": TransaksiServis,
    "penjualan": Order,
    "pelanggan": Pelanggan,
    "produk": Produk,
    "akun": User,
    "kasbon": Kasbon,
    "pengeluaran": Pengeluaran,
    "insiden": Insiden,
}


def _get_model(tipe: str):
    m = MODEL_MAP.get(tipe)
    if not m:
        raise HTTPException(404, f"Tipe '{tipe}' tidak dikenali")
    return m


@router.get("/keranjang/{tipe}")
async def list_keranjang(
    tipe: str,
    page: int = Query(1), per_page: int = Query(15),
    db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko),
):
    """List semua soft-deleted records per tipe."""
    Model = _get_model(tipe)
    
    # Build query: hanya yang sudah dihapus (deleted_at IS NOT NULL)
    has_cabang = hasattr(Model, "cabang_id")
    if has_cabang:
        base = and_(Model.deleted_at.isnot(None), Model.cabang_id == cu.cabang_id)
    else:
        base = Model.deleted_at.isnot(None)

    q = select(Model).where(base).order_by(Model.deleted_at.desc())
    cq = select(func.count()).select_from(Model).where(base)

    offset = (page - 1) * per_page
    r = await db.execute(q.offset(offset).limit(per_page))
    count_r = await db.execute(cq)
    total = count_r.scalar_one()

    rows = r.scalars().all()
    # Convert to dicts safely
    data = []
    for row in rows:
        d = {c.name: getattr(row, c.name) for c in row.__table__.columns}
        data.append(d)

    return {
        "data": data,
        "meta": {
            "total": total,
            "page": page,
            "per_page": per_page,
            "last_page": -(-total // per_page),
        }
    }


@router.post("/keranjang/{tipe}/{id}/restore")
async def restore_item(
    tipe: str, id: int,
    db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko),
):
    """Restore (undelete) satu record."""
    Model = _get_model(tipe)
    r = await db.execute(select(Model).where(Model.id == id, Model.deleted_at.isnot(None)))
    item = r.scalar_one_or_none()
    if not item:
        raise HTTPException(404, "Data tidak ditemukan di keranjang")

    # Cek cabang
    if hasattr(item, "cabang_id") and item.cabang_id != cu.cabang_id:
        raise HTTPException(403, "Tidak bisa restore data cabang lain")

    await db.execute(update(Model).where(Model.id == id).values(deleted_at=None, updated_at=datetime.utcnow()))
    return {"message": f"Data berhasil dipulihkan"}


@router.delete("/keranjang/{tipe}/{id}")
async def permanent_delete(
    tipe: str, id: int,
    db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko),
):
    """Hapus permanen (tidak bisa dikembalikan)."""
    from sqlalchemy import delete as sd
    Model = _get_model(tipe)

    r = await db.execute(select(Model).where(Model.id == id, Model.deleted_at.isnot(None)))
    item = r.scalar_one_or_none()
    if not item:
        raise HTTPException(404, "Data tidak ditemukan di keranjang")

    await db.execute(sd(Model).where(Model.id == id))
    return {"message": "Data dihapus permanen"}


@router.post("/keranjang/{tipe}/bersihkan")
async def bersihkan_keranjang(
    tipe: str,
    db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko),
):
    """Hapus semua data di keranjang (permanent delete all trash) per cabang."""
    from sqlalchemy import delete as sd
    Model = _get_model(tipe)

    if hasattr(Model, "cabang_id"):
        await db.execute(sd(Model).where(Model.deleted_at.isnot(None), Model.cabang_id == cu.cabang_id))
    else:
        await db.execute(sd(Model).where(Model.deleted_at.isnot(None)))
    return {"message": f"Keranjang {tipe} berhasil dikosongkan"}
