"""
Enterprise Redis cache layer.
- TTL per kategori data
- Cache invalidation pattern
- Compressed JSON untuk data besar
"""
import json
import zlib
import logging
from typing import Any, Optional
from functools import wraps

import redis.asyncio as aioredis

from app.core.config import settings

logger = logging.getLogger(__name__)

# ─────────────────────────────────────────────────────────
# TTL CONSTANTS (seconds)
# ─────────────────────────────────────────────────────────
class CacheTTL:
    SHORT = 60           # 1 menit  — data real-time (dashboard count)
    MEDIUM = 300         # 5 menit  — list transaksi
    LONG = 1800          # 30 menit — master data (merek, model, dll)
    VERY_LONG = 86400    # 1 hari   — config, pengaturan toko


# ─────────────────────────────────────────────────────────
# REDIS CLIENT
# ─────────────────────────────────────────────────────────
redis_client: Optional[aioredis.Redis] = None


async def get_redis() -> aioredis.Redis:
    global redis_client
    if redis_client is None:
        redis_client = aioredis.from_url(
            settings.REDIS_URL,
            encoding="utf-8",
            decode_responses=False,  # bytes untuk kompresi
            max_connections=50,
            socket_timeout=5,
            socket_connect_timeout=5,
            retry_on_timeout=True,
        )
    return redis_client


async def close_redis():
    global redis_client
    if redis_client:
        await redis_client.close()
        redis_client = None


# ─────────────────────────────────────────────────────────
# CACHE OPERATIONS
# ─────────────────────────────────────────────────────────
COMPRESS_THRESHOLD = 1024  # compress jika > 1KB


def _encode(data: Any) -> bytes:
    """JSON → bytes dengan kompresi jika data besar."""
    raw = json.dumps(data, default=str, ensure_ascii=False).encode("utf-8")
    if len(raw) > COMPRESS_THRESHOLD:
        return b"gz:" + zlib.compress(raw, level=6)
    return b"js:" + raw


def _decode(raw: bytes) -> Any:
    """Decode bytes kembali ke Python object."""
    if raw.startswith(b"gz:"):
        return json.loads(zlib.decompress(raw[3:]).decode("utf-8"))
    return json.loads(raw[3:].decode("utf-8"))


async def cache_get(key: str) -> Optional[Any]:
    try:
        r = await get_redis()
        raw = await r.get(key)
        if raw is None:
            return None
        return _decode(raw)
    except Exception as e:
        logger.warning(f"Cache get failed [{key}]: {e}")
        return None


async def cache_set(key: str, value: Any, ttl: int = CacheTTL.MEDIUM) -> bool:
    try:
        r = await get_redis()
        await r.setex(key, ttl, _encode(value))
        return True
    except Exception as e:
        logger.warning(f"Cache set failed [{key}]: {e}")
        return False


async def cache_delete(key: str) -> None:
    try:
        r = await get_redis()
        await r.delete(key)
    except Exception as e:
        logger.warning(f"Cache delete failed [{key}]: {e}")


async def cache_delete_pattern(pattern: str) -> int:
    """Hapus semua key yang match pattern (mis: 'pelanggan:*')."""
    try:
        r = await get_redis()
        keys = await r.keys(pattern)
        if keys:
            return await r.delete(*keys)
        return 0
    except Exception as e:
        logger.warning(f"Cache delete pattern failed [{pattern}]: {e}")
        return 0


# ─────────────────────────────────────────────────────────
# CACHE KEY BUILDERS
# ─────────────────────────────────────────────────────────
class CacheKeys:
    @staticmethod
    def dashboard(cabang_id: int, tipe: str) -> str:
        return f"dashboard:{cabang_id}:{tipe}"

    @staticmethod
    def pelanggan_list(cabang_id: int, page: int, q: str) -> str:
        return f"pelanggan:{cabang_id}:p{page}:q{q}"

    @staticmethod
    def produk_list(cabang_id: int, page: int, q: str, tipe: str) -> str:
        return f"produk:{cabang_id}:{tipe}:p{page}:q{q}"

    @staticmethod
    def master_merek(cabang_id: int) -> str:
        return f"master:merek:{cabang_id}"

    @staticmethod
    def master_model(cabang_id: int) -> str:
        return f"master:model:{cabang_id}"

    @staticmethod
    def master_tindakan(cabang_id: int) -> str:
        return f"master:tindakan:{cabang_id}"

    @staticmethod
    def servis_list(cabang_id: int, status: str, page: int) -> str:
        return f"servis:{cabang_id}:{status}:p{page}"

    @staticmethod
    def cabang_config(cabang_id: int) -> str:
        return f"config:cabang:{cabang_id}"

    @staticmethod
    def laporan(cabang_id: int, tipe: str, bulan: int, tahun: int) -> str:
        return f"laporan:{cabang_id}:{tipe}:{tahun}:{bulan}"
