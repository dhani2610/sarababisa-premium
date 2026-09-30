"""
Pelanggan API — optimized pagination, search, CRUD
"""
from fastapi import APIRouter, Depends, Query, HTTPException, Path
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func, and_, or_, update, delete
from sqlalchemy.orm import selectinload
from typing import Optional
from datetime import datetime

from app.core.database import get_db
from app.core.security import get_current_user, require_any
from app.core.cache import cache_get, cache_set, cache_delete_pattern, CacheTTL
from app.models.models import Pelanggan
from app.utils.query import paginate, apply_search, soft_delete_filter

router = APIRouter()


@router.get("/pelanggan")
async def list_pelanggan(
    page: int = Query(1, ge=1),
    per_page: int = Query(15, ge=1, le=100),
    search: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    cabang_id = current_user.cabang_id

    # Base filter — hanya kolom yang dibutuhkan
    base = and_(
        Pelanggan.cabang_id == cabang_id,
        soft_delete_filter(Pelanggan.deleted_at),
    )

    data_q = select(
        Pelanggan.id, Pelanggan.nama, Pelanggan.nomor_hp.label("no_hp"),
        Pelanggan.kategori, Pelanggan.alamat,
        Pelanggan.created_at,
    ).where(base).order_by(Pelanggan.nama.asc())

    count_q = select(func.count(Pelanggan.id)).where(base)

    if search:
        term = f"%{search}%"
        search_cond = or_(
            Pelanggan.nama.ilike(term),
            Pelanggan.nomor_hp.ilike(term),
            Pelanggan.alamat.ilike(term),
        )
        data_q = data_q.where(search_cond)
        count_q = count_q.where(search_cond)

    return await paginate(db, data_q, count_q, page, per_page)


@router.get("/pelanggan/{id}")
async def get_pelanggan(
    id: int = Path(...),
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    result = await db.execute(
        select(Pelanggan).where(
            Pelanggan.id == id,
            Pelanggan.cabang_id == current_user.cabang_id,
            Pelanggan.deleted_at.is_(None),
        )
    )
    pelanggan = result.scalar_one_or_none()
    if not pelanggan:
        raise HTTPException(404, "Pelanggan tidak ditemukan")

    return {
        "id": pelanggan.id,
        "nama": pelanggan.nama,
        "no_hp": pelanggan.no_hp,
        "email": pelanggan.email,
        "alamat": pelanggan.alamat,
        "total_transaksi": pelanggan.total_transaksi,
        "created_at": pelanggan.created_at,
    }


@router.post("/pelanggan", status_code=201)
async def create_pelanggan(
    body: dict,
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    pelanggan = Pelanggan(
        nama=body.get("nama"),
        no_hp=body.get("no_hp"),
        email=body.get("email"),
        alamat=body.get("alamat"),
        cabang_id=current_user.cabang_id,
    )
    db.add(pelanggan)
    await db.flush()
    await cache_delete_pattern(f"pelanggan:{current_user.cabang_id}:*")
    return {"id": pelanggan.id, "message": "Pelanggan berhasil ditambahkan"}


@router.put("/pelanggan/{id}")
async def update_pelanggan(
    id: int,
    body: dict,
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    await db.execute(
        update(Pelanggan)
        .where(Pelanggan.id == id, Pelanggan.cabang_id == current_user.cabang_id)
        .values(
            nama=body.get("nama"),
            no_hp=body.get("no_hp"),
            email=body.get("email"),
            alamat=body.get("alamat"),
            updated_at=datetime.utcnow(),
        )
    )
    await cache_delete_pattern(f"pelanggan:{current_user.cabang_id}:*")
    return {"message": "Pelanggan berhasil diperbarui"}


@router.delete("/pelanggan/{id}")
async def delete_pelanggan(
    id: int,
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    # Soft delete
    await db.execute(
        update(Pelanggan)
        .where(Pelanggan.id == id, Pelanggan.cabang_id == current_user.cabang_id)
        .values(deleted_at=datetime.utcnow())
    )
    await cache_delete_pattern(f"pelanggan:{current_user.cabang_id}:*")
    return {"message": "Pelanggan berhasil dihapus"}
