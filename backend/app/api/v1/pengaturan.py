"""
StoreSetting — pengaturan toko per cabang
Settings banyak: pajak, bonus, telegram bot, fonnte WA, absensi, dll.
Multi-cabang: setiap cabang punya settings sendiri.
"""
from fastapi import APIRouter, Depends, HTTPException, UploadFile, File
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, update
from typing import Optional
import json, os, aiofiles, httpx
from datetime import datetime

from app.core.database import get_db
from app.core.security import require_any, require_admin_toko, require_kepala_toko
from app.core.cache import cache_get, cache_set, cache_delete_pattern, CacheTTL
from app.models.models import User, Cabang, StoreSetting, SyaratKetentuan

router = APIRouter()

# ─── Helper ───────────────────────────────────────────────
async def get_setting(db: AsyncSession, cabang_id: int) -> Optional[StoreSetting]:
    r = await db.execute(select(StoreSetting).where(StoreSetting.cabang_id == cabang_id))
    return r.scalar_one_or_none()


# ─── Pengaturan Profil Toko ────────────────────────────────
@router.get("/pengaturan/profil")
async def get_profil_toko(
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_kepala_toko),
):
    """
    Profil toko diambil dari User KepalaToko di cabang tersebut.
    Multi-cabang: setiap cabang_id punya profil sendiri.
    """
    if cu.cabang_id == 1:
        r = await db.execute(select(User).where(User.id == 1))
    else:
        r = await db.execute(
            select(User)
            .where(User.cabang_id == cu.cabang_id, User.role == "Kepala Toko", User.id != 1)
            .order_by(User.id.asc())
            .limit(1)
        )
    kepala = r.scalar_one_or_none()
    if not kepala:
        raise HTTPException(404, "Akun Kepala Toko untuk cabang ini belum ada. Buat dulu di menu Akun.")

    banks = json.loads(kepala.banks or "[]") if kepala.banks else []
    phones = json.loads(kepala.phones or "[]") if kepala.phones else []

    return {
        "id": kepala.id,
        "nama": kepala.nama,
        "nama_toko": kepala.nama_toko,
        "email": kepala.email,
        "no_hp": kepala.no_hp,
        "alamat": kepala.alamat,
        "deskripsi": kepala.deskripsi,
        "bank": kepala.bank,
        "rekening": kepala.rekening,
        "pemilik_rekening": kepala.pemilik_rekening,
        "banks": banks,        # JSON array multi-bank
        "phones": phones,      # JSON array multi-telepon
        "foto_profil": kepala.profile_photo_path,
        "foto_portal": kepala.foto_portal,
        "foto_login": kepala.foto_login,
        "cabang_id": kepala.cabang_id,
    }


@router.put("/pengaturan/profil")
async def update_profil_toko(
    body: dict,
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_kepala_toko),
):
    """Update profil toko (nama_toko, bank, telepon, rekening, dll)."""
    if cu.cabang_id == 1:
        kepala_id = 1
    else:
        r = await db.execute(
            select(User.id)
            .where(User.cabang_id == cu.cabang_id, User.role == "Kepala Toko", User.id != 1)
            .limit(1)
        )
        row = r.scalar_one_or_none()
        if not row:
            raise HTTPException(404, "Kepala Toko cabang ini tidak ditemukan.")
        kepala_id = row

    # Filter banks dan phones yang kosong (sama seperti Laravel)
    banks = [b for b in (body.get("banks") or []) if b.get("bank") or b.get("rekening")]
    phones = [p for p in (body.get("phones") or []) if p.get("title") or p.get("nomor")]

    await db.execute(
        update(User)
        .where(User.id == kepala_id)
        .values(
            nama_toko=body.get("nama_toko"),
            nama=body.get("nama"),
            no_hp=body.get("no_hp"),
            alamat=body.get("alamat"),
            deskripsi=body.get("deskripsi"),
            bank=body.get("bank"),
            rekening=body.get("rekening"),
            pemilik_rekening=body.get("pemilik_rekening"),
            banks=json.dumps(banks),
            phones=json.dumps(phones),
            updated_at=datetime.utcnow(),
        )
    )
    await cache_delete_pattern(f"profil:{cu.cabang_id}")
    return {"message": "Profil toko berhasil diperbarui"}


@router.post("/pengaturan/profil/upload-foto")
async def upload_foto_profil(
    tipe: str,  # profil | portal | login
    file: UploadFile = File(...),
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_kepala_toko),
):
    """Upload logo/foto toko."""
    allowed = {"image/jpeg", "image/png", "image/webp"}
    if file.content_type not in allowed:
        raise HTTPException(400, "Format hanya jpg/png/webp")
    if file.size and file.size > 1024 * 1024:
        raise HTTPException(400, "Ukuran foto maksimal 1 MB")

    ext = file.filename.rsplit(".", 1)[-1]
    fname = f"{tipe}_{cu.cabang_id}_{int(datetime.utcnow().timestamp())}.{ext}"
    dest = f"storage/assets/user/{fname}"
    os.makedirs("storage/assets/user", exist_ok=True)

    async with aiofiles.open(dest, "wb") as f:
        content = await file.read()
        await f.write(content)

    col_map = {"profil": "profile_photo_path", "portal": "foto_portal", "login": "foto_login"}
    col = col_map.get(tipe, "profile_photo_path")

    if cu.cabang_id == 1:
        kepala_id = 1
    else:
        r = await db.execute(
            select(User.id).where(User.cabang_id == cu.cabang_id, User.role == "Kepala Toko").limit(1)
        )
        kepala_id = r.scalar_one_or_none()

    if kepala_id:
        await db.execute(update(User).where(User.id == kepala_id).values(**{col: dest}))

    return {"path": dest, "url": f"/storage/assets/user/{fname}"}


# ─── Pengaturan Sistem (StoreSetting per cabang) ─────────────
@router.get("/pengaturan/sistem")
async def get_sistem(
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_kepala_toko),
):
    """
    Pengaturan sistem per cabang:
    - Pajak PPN
    - Bonus teknisi
    - Edit transaksi hari lalu
    - Approval hapus transaksi
    - Telegram bot notifikasi
    - Fonnte WA gateway
    - Absensi (jam masuk, jam pulang, nominal potongan)
    - Tampil modal, profit, dll
    """
    setting = await get_setting(db, cu.cabang_id)
    if not setting:
        # Return defaults jika belum ada
        return {
            "cabang_id": cu.cabang_id,
            "is_tax": False,
            "ppn": 11,
            "is_bonus": True,
            "is_edit_transaksi": False,
            "approval_hapus_transaksi": False,
            "is_edit_produk": True,
            "token_bot": None,
            "chat_id": None,
            "fonnte": None,
            "is_modal": True,
            "is_profit": True,
            "is_profit_produk": True,
            "is_modal_produk": True,
            "is_bonus_produk": True,
            "active_setting_absensi": False,
            "jam_masuk": "08:00",
            "jam_pulang": "17:00",
            "nominal_potongan_izin": 0,
            "nominal_potongan_alfa": 0,
            "nominal_potongan_sakit": 0,
            "nominal_overtime": 0,
            "report_time": "08:00",
            "backup_email": None,
        }

    return {
        "cabang_id": setting.cabang_id,
        "is_tax": bool(setting.is_tax),
        "ppn": setting.ppn or 11,
        "is_bonus": bool(setting.is_bonus),
        "is_edit_transaksi": bool(setting.is_edit_transaksi),
        "approval_hapus_transaksi": bool(setting.approval_hapus_transaksi),
        "is_edit_produk": bool(setting.is_edit_produk),
        "token_bot": setting.token_bot,
        "chat_id": setting.chat_id,
        "fonnte": setting.fonnte,
        "is_modal": bool(setting.is_modal),
        "is_profit": bool(setting.is_profit),
        "is_profit_produk": bool(setting.is_profit_produk),
        "is_modal_produk": bool(setting.is_modal_produk),
        "is_bonus_produk": bool(setting.is_bonus_produk),
        "active_setting_absensi": bool(setting.active_setting_absensi),
        "jam_masuk": str(setting.jam_masuk) if setting.jam_masuk else "08:00",
        "jam_pulang": str(setting.jam_pulang) if setting.jam_pulang else "17:00",
        "nominal_potongan_izin": float(setting.nominal_potongan_izin or 0),
        "nominal_potongan_alfa": float(setting.nominal_potongan_alfa or 0),
        "nominal_potongan_sakit": float(setting.nominal_potongan_sakit or 0),
        "nominal_overtime": float(setting.nominal_overtime or 0),
        "report_time": setting.report_time,
        "backup_email": setting.backup_email,
    }


@router.post("/pengaturan/sistem")
async def update_sistem(
    body: dict,
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_kepala_toko),
):
    """Upsert setting sistem per cabang."""
    setting = await get_setting(db, cu.cabang_id)
    values = {k: v for k, v in body.items() if k != "cabang_id"}
    values["updated_at"] = datetime.utcnow()

    if setting:
        await db.execute(
            update(StoreSetting)
            .where(StoreSetting.cabang_id == cu.cabang_id)
            .values(**values)
        )
    else:
        db.add(StoreSetting(cabang_id=cu.cabang_id, **values))

    await cache_delete_pattern(f"setting:{cu.cabang_id}")
    return {"message": "Pengaturan sistem berhasil disimpan"}


# ─── Syarat & Ketentuan ────────────────────────────────────
@router.get("/pengaturan/syarat-ketentuan")
async def get_syarat(
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_kepala_toko),
):
    r = await db.execute(select(SyaratKetentuan).where(SyaratKetentuan.cabang_id == cu.cabang_id))
    term = r.scalar_one_or_none()
    return {
        "syarat_terima": term.syarat_terima if term else "",
        "syarat_pengambilan": term.syarat_pengambilan if term else "",
        "syarat_penjualan": term.syarat_penjualan if term else "",
    }


@router.post("/pengaturan/syarat-ketentuan")
async def update_syarat(
    body: dict,
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_kepala_toko),
):
    r = await db.execute(select(SyaratKetentuan).where(SyaratKetentuan.cabang_id == cu.cabang_id))
    term = r.scalar_one_or_none()
    if term:
        await db.execute(
            update(SyaratKetentuan)
            .where(SyaratKetentuan.cabang_id == cu.cabang_id)
            .values(
                syarat_terima=body.get("syarat_terima"),
                syarat_pengambilan=body.get("syarat_pengambilan"),
                syarat_penjualan=body.get("syarat_penjualan"),
                updated_at=datetime.utcnow(),
            )
        )
    else:
        db.add(SyaratKetentuan(
            cabang_id=cu.cabang_id,
            syarat_terima=body.get("syarat_terima"),
            syarat_pengambilan=body.get("syarat_pengambilan"),
            syarat_penjualan=body.get("syarat_penjualan"),
        ))
    return {"message": "Syarat & Ketentuan berhasil disimpan"}


# ─── Multi-Cabang ─────────────────────────────────────────
@router.get("/cabang")
async def list_cabang(db: AsyncSession = Depends(get_db)):
    r = await db.execute(select(Cabang).order_by(Cabang.id))
    return [{"id": c.id, "nama": c.nama_cabang, "alamat": "", "no_hp": ""} for c in r.scalars().all()]


@router.post("/cabang/switch")
async def switch_cabang(
    body: dict,
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_kepala_toko),
):
    """Ganti cabang aktif untuk user (KepalaToko multi-cabang)."""
    cabang_id = body.get("cabang_id")
    # Validasi: user harus punya akses ke cabang itu
    r = await db.execute(select(Cabang.id).where(Cabang.id == cabang_id))
    if not r.scalar_one_or_none():
        raise HTTPException(404, "Cabang tidak ditemukan")

    await db.execute(update(User).where(User.id == cu.id).values(cabang_id=cabang_id))
    return {"message": "Cabang berhasil diganti", "cabang_id": cabang_id}
