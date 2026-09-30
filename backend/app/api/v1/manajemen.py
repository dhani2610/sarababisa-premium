"""
Manajemen API — kasbon, pengeluaran, inventaris, insiden
"""
from fastapi import APIRouter, Depends, Query, HTTPException
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func, and_, update
from typing import Optional
from datetime import datetime, date

from app.core.database import get_db
from app.core.security import require_any, require_admin_toko
from app.models.models import Kasbon, Pengeluaran, Inventaris, Insiden, User
from app.utils.query import paginate, soft_delete_filter

router = APIRouter()


# ─── Kasbon ───────────────────────────────────────────────
@router.get("/manajemen/kasbon")
async def list_kasbon(
    page: int = Query(1, ge=1), per_page: int = Query(15),
    status: Optional[str] = None, search: Optional[str] = None,
    db: AsyncSession = Depends(get_db), cu=Depends(require_any),
):
    base = and_(Kasbon.cabang_id == cu.cabang_id, soft_delete_filter(Kasbon.deleted_at))
    q = (
        select(Kasbon.id, Kasbon.jumlah, Kasbon.keterangan, Kasbon.status,
               Kasbon.tgl_kasbon, Kasbon.tgl_lunas, Kasbon.created_at,
               User.nama.label("karyawan_nama"))
        .outerjoin(User, User.id == Kasbon.user_id)
        .where(base)
        .order_by(Kasbon.created_at.desc())
    )
    cq = select(func.count(Kasbon.id)).where(base)
    if status:
        q = q.where(Kasbon.status == status)
        cq = cq.where(Kasbon.status == status)
    if search:
        t = f"%{search}%"
        q = q.where(User.nama.ilike(t))
    return await paginate(db, q, cq, page, per_page)


@router.post("/manajemen/kasbon", status_code=201)
async def create_kasbon(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    k = Kasbon(
        user_id=body.get("user_id", cu.id),
        cabang_id=cu.cabang_id,
        jumlah=body.get("jumlah", 0),
        keterangan=body.get("keterangan"),
        tgl_kasbon=body.get("tgl_kasbon", date.today()),
        status="menunggu",
    )
    db.add(k)
    await db.flush()
    return {"id": k.id, "message": "Kasbon diajukan"}


@router.patch("/manajemen/kasbon/{id}/setujui")
async def setujui_kasbon(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_admin_toko)):
    await db.execute(
        update(Kasbon).where(Kasbon.id == id, Kasbon.cabang_id == cu.cabang_id)
        .values(status="disetujui", updated_at=datetime.utcnow())
    )
    return {"message": "Kasbon disetujui"}


@router.patch("/manajemen/kasbon/{id}/tolak")
async def tolak_kasbon(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_admin_toko)):
    await db.execute(
        update(Kasbon).where(Kasbon.id == id, Kasbon.cabang_id == cu.cabang_id)
        .values(status="ditolak", updated_at=datetime.utcnow())
    )
    return {"message": "Kasbon ditolak"}


@router.delete("/manajemen/kasbon/{id}")
async def delete_kasbon(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_admin_toko)):
    await db.execute(
        update(Kasbon).where(Kasbon.id == id, Kasbon.cabang_id == cu.cabang_id)
        .values(deleted_at=datetime.utcnow())
    )
    return {"message": "Kasbon dihapus"}


# ─── Pengeluaran ──────────────────────────────────────────
@router.get("/manajemen/pengeluaran")
async def list_pengeluaran(
    page: int = Query(1), per_page: int = Query(15),
    search: Optional[str] = None,
    date_from: Optional[date] = None, date_to: Optional[date] = None,
    db: AsyncSession = Depends(get_db), cu=Depends(require_any),
):
    base = and_(Pengeluaran.cabang_id == cu.cabang_id, soft_delete_filter(Pengeluaran.deleted_at))
    q = (
        select(Pengeluaran.id, Pengeluaran.nama, Pengeluaran.jumlah,
               Pengeluaran.tipe, Pengeluaran.tgl_pengeluaran,
               Pengeluaran.is_approved, Pengeluaran.bukti, Pengeluaran.created_at,
               User.nama.label("user_nama"))
        .outerjoin(User, User.id == Pengeluaran.user_id)
        .where(base)
        .order_by(Pengeluaran.tgl_pengeluaran.desc())
    )
    cq = select(func.count(Pengeluaran.id)).where(base)
    if date_from:
        q = q.where(Pengeluaran.tgl_pengeluaran >= date_from)
        cq = cq.where(Pengeluaran.tgl_pengeluaran >= date_from)
    if date_to:
        q = q.where(Pengeluaran.tgl_pengeluaran <= date_to)
        cq = cq.where(Pengeluaran.tgl_pengeluaran <= date_to)
    if search:
        t = f"%{search}%"
        q = q.where(Pengeluaran.nama.ilike(t))
        cq = cq.where(Pengeluaran.nama.ilike(t))
    return await paginate(db, q, cq, page, per_page)


@router.post("/manajemen/pengeluaran", status_code=201)
async def create_pengeluaran(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_admin_toko)):
    p = Pengeluaran(
        nama=body.get("nama"),
        jumlah=body.get("jumlah", 0),
        tipe=body.get("tipe"),
        keterangan=body.get("keterangan"),
        tgl_pengeluaran=body.get("tgl_pengeluaran", date.today()),
        user_id=cu.id,
        cabang_id=cu.cabang_id,
    )
    db.add(p)
    await db.flush()
    return {"id": p.id}


@router.delete("/manajemen/pengeluaran/{id}")
async def delete_pengeluaran(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_admin_toko)):
    await db.execute(
        update(Pengeluaran).where(Pengeluaran.id == id, Pengeluaran.cabang_id == cu.cabang_id)
        .values(deleted_at=datetime.utcnow())
    )
    return {"message": "Pengeluaran dihapus"}


# ─── Inventaris ───────────────────────────────────────────
@router.get("/manajemen/inventaris")
async def list_inventaris(
    page: int = Query(1), per_page: int = Query(15),
    search: Optional[str] = None,
    db: AsyncSession = Depends(get_db), cu=Depends(require_any),
):
    base = and_(Inventaris.cabang_id == cu.cabang_id, soft_delete_filter(Inventaris.deleted_at))
    q = select(Inventaris.id, Inventaris.nama, Inventaris.kode, Inventaris.kondisi,
               Inventaris.lokasi, Inventaris.harga, Inventaris.tgl_beli).where(base)
    cq = select(func.count(Inventaris.id)).where(base)
    if search:
        t = f"%{search}%"
        q = q.where(Inventaris.nama.ilike(t))
        cq = cq.where(Inventaris.nama.ilike(t))
    return await paginate(db, q, cq, page, per_page)


@router.post("/manajemen/inventaris", status_code=201)
async def create_inventaris(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_admin_toko)):
    inv = Inventaris(
        nama=body.get("nama"), kode=body.get("kode"),
        kondisi=body.get("kondisi", "baik"), lokasi=body.get("lokasi"),
        keterangan=body.get("keterangan"), harga=body.get("harga", 0),
        tgl_beli=body.get("tgl_beli"), cabang_id=cu.cabang_id,
    )
    db.add(inv)
    await db.flush()
    return {"id": inv.id}
