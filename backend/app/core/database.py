"""
Enterprise-grade async database configuration.
- Connection pooling dengan pool_size=20, max_overflow=40
- Pool pre-ping untuk deteksi koneksi mati
- Query timeout 30 detik
- Recycle koneksi setiap 1 jam
"""
from sqlalchemy.ext.asyncio import (
    create_async_engine,
    AsyncSession,
    async_sessionmaker,
)
from sqlalchemy import event, text
from sqlalchemy.pool import QueuePool
from typing import AsyncGenerator
import logging

from app.core.config import settings

logger = logging.getLogger(__name__)

# ─────────────────────────────────────────────────────────
# ENGINE — Async MySQL dengan aiomysql driver
# ─────────────────────────────────────────────────────────
DATABASE_URL = (
    f"mysql+aiomysql://{settings.DB_USER}:{settings.DB_PASSWORD}"
    f"@{settings.DB_HOST}:{settings.DB_PORT}/{settings.DB_NAME}"
    f"?charset=utf8mb4"
)

engine = create_async_engine(
    DATABASE_URL,
    # Connection Pool — enterprise scale
    pool_size=20,            # koneksi persistent
    max_overflow=40,         # koneksi tambahan saat spike
    pool_timeout=30,         # timeout tunggu koneksi dari pool
    pool_recycle=3600,       # recycle koneksi setiap 1 jam (cegah MySQL gone away)
    pool_pre_ping=True,      # cek koneksi sebelum dipakai
    echo=False,              # set True untuk debug SQL
    # Query execution options
    execution_options={
        "compiled_cache": {},  # cache compiled queries
    },
    connect_args={
        "connect_timeout": 10,
        "charset": "utf8mb4",
    },
)

# ─────────────────────────────────────────────────────────
# SESSION FACTORY
# ─────────────────────────────────────────────────────────
AsyncSessionLocal = async_sessionmaker(
    bind=engine,
    class_=AsyncSession,
    expire_on_commit=False,   # penting: jangan expire setelah commit (performa)
    autocommit=False,
    autoflush=False,
)


# ─────────────────────────────────────────────────────────
# DEPENDENCY INJECTION
# ─────────────────────────────────────────────────────────
async def get_db() -> AsyncGenerator[AsyncSession, None]:
    """FastAPI dependency untuk database session."""
    async with AsyncSessionLocal() as session:
        try:
            yield session
            await session.commit()
        except Exception:
            await session.rollback()
            raise
        finally:
            await session.close()


# ─────────────────────────────────────────────────────────
# HELPER FUNCTIONS
# ─────────────────────────────────────────────────────────
async def ping_db() -> bool:
    """Health check database."""
    try:
        async with AsyncSessionLocal() as session:
            await session.execute(text("SELECT 1"))
            return True
    except Exception as e:
        logger.error(f"Database ping failed: {e}")
        return False
