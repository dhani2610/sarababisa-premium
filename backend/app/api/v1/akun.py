"""
Akun (User Management) API — KepalaToko only
Multi-cabang: KepalaToko bisa kelola akun untuk cabang mereka.
"""
from fastapi import APIRouter, Depends, Query, HTTPException
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func, and_, update, or_
from typing import Optional
from datetime import datetime

from app.core.database import get_db
from app.core.security import require_kepala_toko, require_any
from app.core.security import hash_password
from app.models.models import User, Cabang
from app.utils.query import paginate

router = APIRouter()


@router.get("/akun")
async def list_akun(
    page: int = Query(1, ge=1),
    per_page: int = Query(15, ge=1, le=100),
    search: Optional[str] = Query(None),
    role: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_kepala_toko),
):
    """List semua user di cabang — multi-cabang: filter by cabang_id."""
    base = and_(
        or_(User.cabang_id == cu.cabang_id, User.id == 1),
        User.deleted_at.is_(None),
        User.id != 1,  # exclude superadmin dari list
    )
    # KepalaToko hanya lihat akun di cabangnya
    if cu.cabang_id != 1:
        base = and_(User.cabang_id == cu.cabang_id, User.deleted_at.is_(None))

    q = select(
        User.id, User.name.label("nama"), User.username, User.email, User.nomor_hp.label("no_hp"),
        User.role, User.cabang_id, User.deleted_at, User.created_at,
        User.nik, User.alamat, User.bagian_teknisi, User.persen.label("persen_hardware"),
        Cabang.nama.label("cabang_nama"),
    ).outerjoin(Cabang, Cabang.id == User.cabang_id).where(base).order_by(User.name)

    cq = select(func.count(User.id)).where(base)

    if search:
        t = f"%{search}%"
        cond = or_(User.nama.ilike(t), User.username.ilike(t), User.email.ilike(t))
        q = q.where(cond)
        cq = cq.where(cond)
    if role:
        q = q.where(User.role == role)
        cq = cq.where(User.role == role)

    return await paginate(db, q, cq, page, per_page)


@router.get("/akun/{id}")
async def get_akun(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    r = await db.execute(select(User).where(User.id == id, User.deleted_at.is_(None)))
    user = r.scalar_one_or_none()
    if not user:
        raise HTTPException(404, "Akun tidak ditemukan")
    from app.models.models import UserCabangAkses
    r_cabs = await db.execute(select(UserCabangAkses.cabang_id).where(UserCabangAkses.user_id == id))
    cabang_ids = [c[0] for c in r_cabs.all()]
    if user.cabang_id and user.cabang_id not in cabang_ids:
        cabang_ids.append(user.cabang_id)

    return {
        "id": user.id, "nama": user.nama, "username": user.username,
        "email": user.email, "no_hp": user.no_hp, "role": user.role,
        "cabang_id": user.cabang_id, "cabang_ids": cabang_ids,
        "is_active": user.is_active,
        "shift_id": user.shift_id, "foto": user.profile_photo_path,
        "gaji_pokok": float(user.gaji_pokok or 0),
    }


@router.post("/akun", status_code=201)
async def create_akun(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    from app.models.models import UserCabangAkses
    # Cek username unik
    r = await db.execute(select(User.id).where(User.username == body.get("username")))
    if r.scalar_one_or_none():
        raise HTTPException(400, "Username sudah digunakan")

    user = User(
        nama=body["nama"],
        username=body["username"],
        email=body.get("email"),
        no_hp=body.get("no_hp"),
        role=body.get("role", "Teknisi"),
        cabang_id=body.get("cabang_id", cu.cabang_id),
        password=hash_password(body["password"]),
        gaji_pokok=body.get("gaji_pokok", 0),
        shift_id=body.get("shift_id"),
        is_active=True,
    )
    db.add(user)
    await db.flush()

    # Multi-cabang akses jika ada
    cabang_ids = body.get("cabang_ids", [])
    for cid in set(cabang_ids):
        db.add(UserCabangAkses(user_id=user.id, cabang_id=cid))

    return {"id": user.id, "message": "Akun berhasil dibuat"}


@router.put("/akun/{id}")
async def update_akun(id: int, body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    from app.models.models import UserCabangAkses
    from sqlalchemy import delete
    values = {
        "nama": body.get("nama"),
        "email": body.get("email"),
        "no_hp": body.get("no_hp"),
        "role": body.get("role"),
        "cabang_id": body.get("cabang_id"),
        "gaji_pokok": body.get("gaji_pokok"),
        "shift_id": body.get("shift_id"),
        "is_active": body.get("is_active"),
        "updated_at": datetime.utcnow(),
    }
    if body.get("password"):
        values["password"] = hash_password(body["password"])
    values = {k: v for k, v in values.items() if v is not None}
    await db.execute(update(User).where(User.id == id).values(**values))

    # Update multi-cabang akses jika disediakan
    if "cabang_ids" in body:
        await db.execute(delete(UserCabangAkses).where(UserCabangAkses.user_id == id))
        for cid in set(body.get("cabang_ids", [])):
            db.add(UserCabangAkses(user_id=id, cabang_id=cid))

    return {"message": "Akun berhasil diperbarui"}



@router.delete("/akun/{id}")
async def delete_akun(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    if id == cu.id:
        raise HTTPException(400, "Tidak bisa menghapus akun sendiri")
    await db.execute(update(User).where(User.id == id).values(deleted_at=datetime.utcnow()))
    return {"message": "Akun dihapus"}


@router.post("/akun/delete-batch")
async def delete_akun_batch(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    ids = body.get("ids", [])
    if cu.id in ids:
        ids = [i for i in ids if i != cu.id]
    await db.execute(update(User).where(User.id.in_(ids)).values(deleted_at=datetime.utcnow()))
    return {"message": f"{len(ids)} akun dihapus"}


@router.get("/akun/{id}/bonus-detail")
async def bonus_detail(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    """Detail perhitungan bonus teknisi/sales — filter berdasarkan bulan."""
    from sqlalchemy import extract
    from datetime import datetime
    bulan = datetime.now().month
    tahun = datetime.now().year

    from app.models.models import TransaksiServis, TeknisiTarget
    r_target = await db.execute(
        select(TeknisiTarget).where(
            TeknisiTarget.user_id == id,
            TeknisiTarget.cabang_id == cu.cabang_id,
            extract("month", TeknisiTarget.created_at) == bulan,
            extract("year", TeknisiTarget.created_at) == tahun,
        )
    )
    targets = r_target.scalars().all()

    r_servis = await db.execute(
        select(
            func.count(TransaksiServis.id).label("total"),
            func.sum(TransaksiServis.profit).label("total_profit"),
        ).where(
            TransaksiServis.teknisi_id == id,
            TransaksiServis.cabang_id == cu.cabang_id,
            TransaksiServis.status == "sudah_diambil",
            extract("month", TransaksiServis.tgl_selesai) == bulan,
            extract("year", TransaksiServis.tgl_selesai) == tahun,
        )
    )
    servis = r_servis.mappings().first()

    return {
        "targets": [{"tipe": t.tipe, "item": t.item, "nominal": float(t.nominal or 0), "bonus_nominal": float(t.bonus_nominal or 0)} for t in targets],
        "pencapaian": {
            "total_servis": servis["total"] or 0,
            "total_profit": float(servis["total_profit"] or 0),
        }
    }
