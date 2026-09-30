"""
Nomor Nota / Invoice Generator
Generate nomor unik per cabang menggunakan Redis atomic counter.
Format: {PREFIX}-{YYYYMMDD}-{6-digit counter}
Contoh: SRV-20240929-000001, INV-20240929-000123
Redis key: nomor:{prefix}:{cabang_id}:{date}
"""
import asyncio
from datetime import date
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func

try:
    from app.core.cache import get_redis
    HAS_REDIS = True
except ImportError:
    HAS_REDIS = False


async def generate_no_nota(db: AsyncSession, cabang_id: int, prefix: str = "SRV") -> str:
    """
    Generate nomor nota atomik per cabang per hari.
    Fallback ke random jika Redis tidak tersedia.
    """
    today = date.today().strftime("%Y%m%d")
    key = f"nomor:{prefix}:{cabang_id}:{today}"

    if HAS_REDIS:
        try:
            redis = await get_redis()
            # Atomic increment + set expiry 48 jam
            counter = await redis.incr(key)
            await redis.expire(key, 172800)  # 48 jam
            return f"{prefix}-{today}-{counter:06d}"
        except Exception:
            pass  # Fallback ke DB counter

    # Fallback: hitung dari DB per hari ini
    # Gunakan model sesuai prefix
    model_map = {
        "SRV": "transaksi_servis",
        "INV": "orders",
        "PO": "purchases",
        "TRF": "transfer_stoks",
        "RTR": "returs",
        "TT": "tukar_tambah",
        "KSB": "kasbons",
        "PGK": "pengeluarans",
        "GRS": "history_garansis",
    }

    from sqlalchemy import text
    table = model_map.get(prefix, "transaksi_servis")
    col = "no_nota" if prefix == "SRV" else "no_invoice" if prefix == "INV" else "no_po" if prefix == "PO" else "no_" + prefix.lower()

    try:
        r = await db.execute(
            text(f"SELECT COUNT(*) FROM {table} WHERE cabang_id = :cid AND DATE(created_at) = CURDATE()"),
            {"cid": cabang_id}
        )
        count = r.scalar_one() + 1
        return f"{prefix}-{today}-{count:06d}"
    except Exception:
        import random
        return f"{prefix}-{today}-{random.randint(1000, 9999):06d}"
