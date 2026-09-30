"""
Security utilities — JWT + RBAC + Multi-Cabang Context
Multi-cabang: user punya cabang_id, semua query difilter per cabang.
StoreSetting: per cabang, cek setting sebelum operasi.
"""
from datetime import datetime, timedelta, timezone
from typing import Optional, List
from jose import jwt, JWTError
from passlib.context import CryptContext
from fastapi import Depends, HTTPException, status, Request
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select

from app.core.config import settings

import bcrypt

security = HTTPBearer()


def verify_password(plain: str, hashed: str) -> bool:
    try:
        # Konversi format hash $2y$ Laravel ke $2b$ agar kompatibel dengan library python bcrypt
        formatted_hash = hashed.replace("$2y$", "$2b$").encode("utf-8")
        return bcrypt.checkpw(plain.encode("utf-8")[:72], formatted_hash)
    except Exception:
        return False


def hash_password(password: str) -> str:
    salt = bcrypt.gensalt()
    return bcrypt.hashpw(password.encode("utf-8")[:72], salt).decode("utf-8")



def create_access_token(data: dict) -> str:
    expire = datetime.now(timezone.utc) + timedelta(minutes=settings.JWT_ACCESS_TOKEN_EXPIRE_MINUTES)
    return jwt.encode(
        {**data, "exp": expire, "type": "access"},
        settings.JWT_SECRET,
        algorithm=settings.JWT_ALGORITHM,
    )


def create_refresh_token(data: dict) -> str:
    expire = datetime.now(timezone.utc) + timedelta(days=settings.JWT_REFRESH_TOKEN_EXPIRE_DAYS)
    return jwt.encode(
        {**data, "exp": expire, "type": "refresh"},
        settings.JWT_SECRET,
        algorithm=settings.JWT_ALGORITHM,
    )


def verify_token(token: str) -> Optional[dict]:
    try:
        return jwt.decode(token, settings.JWT_SECRET, algorithms=[settings.JWT_ALGORITHM])
    except JWTError:
        return None


class UserContext:
    """User context yang dibawa di setiap request — mirip Auth::user() di Laravel."""
    def __init__(self, payload: dict):
        self.id: int = int(payload["sub"])
        self.username: str = payload["username"]
        self.nama: str = payload.get("nama", "")
        self.role: str = payload["role"]
        self.cabang_id: int = int(payload.get("cabang_id", 1))
        self.foto: Optional[str] = payload.get("foto")
        self.assigned_cabangs: Optional[list] = payload.get("assigned_cabangs")
        # Multi-cabang: ID cabang yang bisa diakses user
        self.assigned_cabang_ids: List[int] = payload.get("assigned_cabang_ids", [self.cabang_id])
        role_clean = (self.role or "").replace(" ", "").replace("_", "").lower()
        self.is_kepala_toko: bool = role_clean in ("kepalatoko", "owner", "superadmin")
        self.is_admin_toko: bool = role_clean in ("kepalatoko", "admintoko", "owner", "superadmin")
        self.is_teknisi: bool = role_clean == "teknisi"
        self.is_sales: bool = role_clean == "sales"

    def can_access_cabang(self, cabang_id: int) -> bool:
        """Cek apakah user bisa akses cabang tertentu (KepalaToko multi-cabang)."""
        if self.is_kepala_toko:
            return cabang_id in self.assigned_cabang_ids
        return cabang_id == self.cabang_id


async def get_current_user(
    credentials: HTTPAuthorizationCredentials = Depends(security),
) -> UserContext:
    token = credentials.credentials
    payload = verify_token(token)
    if not payload or payload.get("type") != "access":
        raise HTTPException(status_code=401, detail="Token tidak valid atau sudah kadaluarsa")
    return UserContext(payload)


# ─── Role Middleware ────────────────────────────────────────
def _norm(r: str) -> str:
    return (r or "").replace(" ", "").replace("_", "").lower()


def require_roles(*roles: str):
    """Role-based access control dependency — sama seperti ensureUserRole di Laravel."""
    norm_allowed = {_norm(r) for r in roles}
    # Kepala Toko or superadmin always has access
    norm_allowed.update({"kepalatoko", "owner", "superadmin"})

    async def checker(current_user: UserContext = Depends(get_current_user)) -> UserContext:
        user_role_norm = _norm(current_user.role)
        if user_role_norm not in norm_allowed:
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN,
                detail=f"Akses ditolak. Role '{current_user.role}' tidak memiliki izin untuk fitur ini.",
            )
        return current_user
    return checker


# ─── Setting-Aware Middleware ───────────────────────────────
async def check_edit_transaksi_allowed(
    current_user: UserContext,
    db: AsyncSession,
    tgl_transaksi: datetime,
) -> bool:
    """
    Cek apakah user boleh edit transaksi hari lalu.
    Logic: StoreSetting.is_edit_transaksi || hari ini || KepalaToko
    Sama dengan logic di Laravel TransaksiServisController.
    """
    from app.models.models import StoreSetting
    from datetime import date

    if current_user.is_kepala_toko:
        return True  # KepalaToko selalu bisa edit

    if tgl_transaksi.date() == date.today():
        return True  # Transaksi hari ini selalu bisa edit

    r = await db.execute(
        select(StoreSetting.is_edit_transaksi)
        .where(StoreSetting.cabang_id == current_user.cabang_id)
    )
    is_edit = r.scalar_one_or_none()
    return bool(is_edit)


async def check_approval_hapus_required(db: AsyncSession, cabang_id: int) -> bool:
    """
    Cek apakah hapus transaksi perlu approval KepalaToko.
    StoreSetting.approval_hapus_transaksi per cabang.
    """
    from app.models.models import StoreSetting
    r = await db.execute(
        select(StoreSetting.approval_hapus_transaksi)
        .where(StoreSetting.cabang_id == cabang_id)
    )
    val = r.scalar_one_or_none()
    return bool(val)


# ─── Role Shortcuts ────────────────────────────────────────
require_kepala_toko = require_roles("Kepala Toko", "KepalaToko")
require_admin_toko  = require_roles("Kepala Toko", "KepalaToko", "Admin Toko", "AdminToko")
require_teknisi     = require_roles("Kepala Toko", "KepalaToko", "Admin Toko", "AdminToko", "Teknisi")
require_sales       = require_roles("Kepala Toko", "KepalaToko", "Admin Toko", "AdminToko", "Sales")
require_any         = require_roles("Kepala Toko", "KepalaToko", "Admin Toko", "AdminToko", "Teknisi", "Sales")

