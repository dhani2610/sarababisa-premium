"""
Auth endpoints — login, refresh token, me
"""
from fastapi import APIRouter, Depends, HTTPException, status, Request
from fastapi.security import OAuth2PasswordRequestForm
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select
from datetime import timedelta

from app.core.database import get_db
from app.core.security import (
    verify_password, create_access_token, create_refresh_token,
    verify_token, get_current_user
)
from app.core.config import settings
from app.models.models import User
from app.schemas.auth import TokenResponse, UserMe, LoginRequest

router = APIRouter()


@router.post("/login", response_model=TokenResponse)
async def login(
    form_data: LoginRequest,
    request: Request,
    db: AsyncSession = Depends(get_db),
):
    # Query user by username — indexed
    result = await db.execute(
        select(User).where(
            User.username == form_data.username,
            User.deleted_at.is_(None),
        )
    )
    user = result.scalar_one_or_none()

    if not user or not verify_password(form_data.password, user.password):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Username atau password salah",
        )

    # Query multi-cabang akses
    from app.models.models import Cabang, UserCabangAkses
    normalized_role = "KepalaToko" if "Kepala" in (user.role or "") else user.role
    if user.id == 1 or normalized_role == "KepalaToko":
        # KepalaToko / Superadmin dapat akses semua cabang yang aktif
        r_all_cab = await db.execute(select(Cabang.id, Cabang.nama_cabang))
        all_cab_rows = r_all_cab.all()
        assigned_ids = [c[0] for c in all_cab_rows]
        assigned_cabangs = [{"id": c[0], "nama": c[1]} for c in all_cab_rows]
    else:
        r_cab = await db.execute(
            select(Cabang.id, Cabang.nama_cabang)
            .join(UserCabangAkses, UserCabangAkses.cabang_id == Cabang.id)
            .where(UserCabangAkses.user_id == user.id)
        )
        extra_cabs = [{"id": r[0], "nama": r[1]} for r in r_cab.all()]
        # Tambahkan cabang utama jika belum ada
        r_home = await db.execute(select(Cabang.id, Cabang.nama_cabang).where(Cabang.id == user.cabang_id))
        home_cab = r_home.first()
        cab_map = {}
        if home_cab:
            cab_map[home_cab[0]] = home_cab[1]
        for ec in extra_cabs:
            cab_map[ec["id"]] = ec["nama"]
        assigned_ids = list(cab_map.keys())
        assigned_cabangs = [{"id": cid, "nama": cnm} for cid, cnm in cab_map.items()]

    user_nama = user.name or user.username
    user_foto = user.profile_photo_path

    payload = {
        "sub": str(user.id),
        "username": user.username,
        "nama": user_nama,
        "role": normalized_role,
        "cabang_id": user.cabang_id or 1,
        "assigned_cabang_ids": assigned_ids,
    }

    access_token = create_access_token(payload)
    refresh_token = create_refresh_token({"sub": str(user.id)})

    return TokenResponse(
        access_token=access_token,
        refresh_token=refresh_token,
        token_type="bearer",
        user=UserMe(
            id=user.id,
            nama=user_nama,
            username=user.username,
            role=normalized_role,
            cabang_id=user.cabang_id or 1,
            foto=user_foto,
            assigned_cabang_ids=assigned_ids,
            assigned_cabangs=assigned_cabangs,
        ),
    )



@router.post("/refresh", response_model=TokenResponse)
async def refresh(refresh_token: str, db: AsyncSession = Depends(get_db)):
    payload = verify_token(refresh_token)
    if not payload or payload.get("type") != "refresh":
        raise HTTPException(status_code=401, detail="Refresh token tidak valid")

    user_id = int(payload["sub"])
    result = await db.execute(
        select(User).where(User.id == user_id, User.deleted_at.is_(None))
    )
    user = result.scalar_one_or_none()
    if not user:
        raise HTTPException(status_code=401, detail="User tidak ditemukan")

    normalized_role = "KepalaToko" if "Kepala" in (user.role or "") else user.role
    user_nama = user.name or user.username
    user_foto = user.profile_photo_path

    token_payload = {
        "sub": str(user.id),
        "username": user.username,
        "nama": user_nama,
        "role": normalized_role,
        "cabang_id": user.cabang_id or 1,
    }

    return TokenResponse(
        access_token=create_access_token(token_payload),
        refresh_token=create_refresh_token({"sub": str(user.id)}),
        token_type="bearer",
        user=UserMe(
            id=user.id,
            nama=user_nama,
            username=user.username,
            role=normalized_role,
            cabang_id=user.cabang_id or 1,
            foto=user_foto,
        ),
    )


@router.get("/me", response_model=UserMe)
async def me(current_user=Depends(get_current_user)):
    return current_user


@router.post("/switch-cabang")
async def switch_cabang(
    body: dict,
    db: AsyncSession = Depends(get_db),
    current_user=Depends(get_current_user),
):
    """
    Switch active cabang_id for multi-cabang user.
    Menghasilkan access token baru dengan cabang_id yang dipilih.
    """
    from app.models.models import Cabang
    target_cabang_id = int(body.get("cabang_id", 0))

    if not current_user.can_access_cabang(target_cabang_id):
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Anda tidak memiliki izin untuk mengakses cabang ini",
        )

    r_cab = await db.execute(select(Cabang).where(Cabang.id == target_cabang_id))
    target_cab = r_cab.scalar_one_or_none()
    if not target_cab:
        raise HTTPException(status_code=404, detail="Cabang tidak ditemukan")

    payload = {
        "sub": str(current_user.id),
        "username": current_user.username,
        "nama": current_user.nama,
        "role": current_user.role,
        "cabang_id": target_cabang_id,
        "assigned_cabang_ids": current_user.assigned_cabang_ids,
    }

    access_token = create_access_token(payload)
    return {
        "access_token": access_token,
        "token_type": "bearer",
        "active_cabang_id": target_cabang_id,
        "active_cabang_nama": target_cab.nama,
        "message": f"Berhasil beralih ke cabang {target_cab.nama}",
        "user": UserMe(
            id=current_user.id,
            nama=current_user.nama,
            username=current_user.username,
            role=current_user.role,
            cabang_id=target_cabang_id,
            foto=current_user.foto,
            assigned_cabang_ids=current_user.assigned_cabang_ids,
            assigned_cabangs=current_user.assigned_cabangs,
        ),
    }


@router.post("/logout")
async def logout():
    # Token-based auth — client hapus token
    return {"message": "Logout berhasil"}

