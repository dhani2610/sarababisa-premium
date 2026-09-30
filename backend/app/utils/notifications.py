"""
Notification Service — Telegram Bot + Fonnte WhatsApp
Per cabang: setiap cabang punya token sendiri dari StoreSetting.
Semua notif fire-and-forget (tidak gagalkan transaksi).
"""
import asyncio
import httpx
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select
from typing import Optional


async def _get_setting(db: AsyncSession, cabang_id: int):
    """Ambil StoreSetting per cabang."""
    try:
        from app.models.models import StoreSetting
        r = await db.execute(select(StoreSetting).where(StoreSetting.cabang_id == cabang_id))
        return r.scalar_one_or_none()
    except Exception:
        return None


async def kirim_telegram(db: AsyncSession, cabang_id: int, pesan: str):
    """Kirim pesan ke Telegram Bot cabang."""
    setting = await _get_setting(db, cabang_id)
    if not setting:
        return
    token = getattr(setting, "telegram_token", None) or getattr(setting, "bot_token", None)
    chat_id = getattr(setting, "telegram_chat_id", None)
    if not token or not chat_id:
        return
    try:
        async with httpx.AsyncClient(timeout=5.0) as client:
            await client.post(
                f"https://api.telegram.org/bot{token}/sendMessage",
                json={"chat_id": chat_id, "text": pesan, "parse_mode": "Markdown"},
            )
    except Exception:
        pass


async def kirim_whatsapp(db: AsyncSession, cabang_id: int, no_hp: str, pesan: str):
    """Kirim WA via Fonnte per cabang."""
    setting = await _get_setting(db, cabang_id)
    if not setting:
        return
    token = getattr(setting, "fonnte_token", None)
    if not token or not no_hp:
        return
    # Normalize nomor
    no = no_hp.replace("-", "").replace(" ", "").replace("+", "")
    if no.startswith("0"):
        no = "62" + no[1:]
    try:
        async with httpx.AsyncClient(timeout=5.0) as client:
            await client.post(
                "https://api.fonnte.com/send",
                headers={"Authorization": token},
                data={"target": no, "message": pesan},
            )
    except Exception:
        pass


# ─── Template Notif Servis ──────────────────────────────────
async def notif_servis_baru(db: AsyncSession, cabang_id: int, data: dict):
    """Notifikasi Telegram saat servis baru masuk."""
    pesan = (
        "*SERVIS BARU MASUK* \n\n"
        "Nomor: " + str(data.get("no_nota", "-")) + "\n"
        "Pelanggan: " + str(data.get("pelanggan_nama", "-")) + "\n"
        "Barang: " + str(data.get("nama_barang", "-")) + "\n"
        "Kerusakan: " + str(data.get("kerusakan", "-")) + "\n"
        "Est. Biaya: Rp " + "{:,.0f}".format(float(data.get("estimasi_biaya", 0))) + "\n"
        "DP: Rp " + "{:,.0f}".format(float(data.get("dp", 0))) + "\n"
        "Tgl Masuk: " + str(data.get("tgl_masuk", "-")) + "\n"
        "Diterima oleh: " + str(data.get("penerima", "-"))
    )
    await kirim_telegram(db, cabang_id, pesan)


async def notif_servis_bisa_diambil(
    db: AsyncSession, cabang_id: int, data: dict, no_hp: Optional[str] = None
):
    """Notifikasi saat servis selesai — Telegram + WA ke pelanggan."""
    pesan_tg = (
        "*SERVIS SELESAI - BISA DIAMBIL* \n\n"
        "Nomor: " + str(data.get("no_nota", "-")) + "\n"
        "Pelanggan: " + str(data.get("pelanggan_nama", "-")) + "\n"
        "Barang: " + str(data.get("nama_barang", "-")) + "\n"
        "Tindakan: " + str(data.get("tindakan", "-")) + "\n"
        "Total: Rp " + "{:,.0f}".format(float(data.get("total_biaya", 0))) + "\n"
        "Sisa Bayar: Rp " + "{:,.0f}".format(float(data.get("sisa_bayar", 0)))
    )
    await kirim_telegram(db, cabang_id, pesan_tg)

    if no_hp:
        pesan_wa = (
            "Halo " + str(data.get("pelanggan_nama", "")) + ",\n"
            "HP/Perangkat Anda sudah selesai diperbaiki dan siap diambil.\n\n"
            "No Servis: " + str(data.get("no_nota", "-")) + "\n"
            "Total Biaya: Rp " + "{:,.0f}".format(float(data.get("total_biaya", 0))) + "\n"
            "Sisa Bayar: Rp " + "{:,.0f}".format(float(data.get("sisa_bayar", 0))) + "\n\n"
            "Terima kasih telah mempercayakan servis kepada kami."
        )
        await kirim_whatsapp(db, cabang_id, no_hp, pesan_wa)


async def notif_servis_sudah_diambil(db: AsyncSession, cabang_id: int, data: dict):
    """Notifikasi saat barang diambil pelanggan — Telegram saja."""
    sisa = data.get("sisa_bayar", 0)
    status_bayar = "Lunas" if not sisa or float(sisa) <= 0 else "Sisa Rp {:,.0f}".format(float(sisa))
    pesan = (
        "*BARANG SUDAH DIAMBIL* \n\n"
        "Nomor: " + str(data.get("no_nota", "-")) + "\n"
        "Pelanggan: " + str(data.get("pelanggan_nama", "-")) + "\n"
        "Total: Rp " + "{:,.0f}".format(float(data.get("total_biaya", 0))) + "\n"
        "Status Bayar: " + status_bayar
    )
    await kirim_telegram(db, cabang_id, pesan)


async def notif_penjualan_baru(db: AsyncSession, cabang_id: int, data: dict):
    """Notifikasi Telegram saat transaksi POS baru selesai."""
    pesan = (
        "*TRANSAKSI PENJUALAN BARU* \n\n"
        "Invoice: " + str(data.get("no_invoice", "-")) + "\n"
        "Pelanggan: " + str(data.get("pelanggan_nama", "-")) + "\n"
        "Total: Rp " + "{:,.0f}".format(float(data.get("total", 0))) + "\n"
        "Metode: " + str(data.get("metode_bayar", "-")) + "\n"
        "Kasir: " + str(data.get("kasir", "-"))
    )
    await kirim_telegram(db, cabang_id, pesan)


async def notif_pengeluaran(db: AsyncSession, cabang_id: int, data: dict):
    """Notifikasi pengeluaran baru."""
    pesan = (
        "*PENGELUARAN BARU* \n\n"
        "Kategori: " + str(data.get("kategori", "-")) + "\n"
        "Nominal: Rp " + "{:,.0f}".format(float(data.get("nominal", 0))) + "\n"
        "Keterangan: " + str(data.get("keterangan", "-")) + "\n"
        "Oleh: " + str(data.get("user_nama", "-"))
    )
    await kirim_telegram(db, cabang_id, pesan)
