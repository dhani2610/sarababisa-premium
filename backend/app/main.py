from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from fastapi.staticfiles import StaticFiles
from contextlib import asynccontextmanager
import os

from app.core.config import settings
from app.core.database import engine

# ── Import all routers ──────────────────────────────────
from app.api.v1 import auth, dashboard, akun, pelanggan, servis
from app.api.v1 import produk, master, laporan, manajemen, karyawan
from app.api.v1 import pengaturan, pos, transfer_stok, target
from app.api.v1 import investor, keranjang, transaksi_produk
from app.api.v1.public import router_public, router_arsip


STORAGE_DIRS = [
    "storage", "storage/uploads", "storage/backups", "storage/exports",
    "storage/assets/user", "storage/servis", "storage/produk",
]
for _d in STORAGE_DIRS:
    os.makedirs(_d, exist_ok=True)


@asynccontextmanager
async def lifespan(app: FastAPI):
    for d in STORAGE_DIRS:
        os.makedirs(d, exist_ok=True)
    yield



app = FastAPI(
    title="SarabaBisa Premium API",
    description="Backend API for SarabaBisa Premium - Toko Servis & Penjualan Multi Cabang",
    version="2.0.0",
    lifespan=lifespan,
    docs_url="/api/docs",
    redoc_url="/api/redoc",
)

# ── CORS ───────────────────────────────────────────────
app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        settings.FRONTEND_URL,
        "http://localhost:3000",
        "http://localhost:3001",
        "http://127.0.0.1:3000",
    ],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# ── Static files ───────────────────────────────────────
app.mount("/storage", StaticFiles(directory="storage"), name="storage")

# ── Public routes (no auth) ────────────────────────────
app.include_router(router_public, prefix="/api/v1", tags=["Public"])

# ── Auth ───────────────────────────────────────────────
app.include_router(auth.router, prefix="/api/v1/auth", tags=["Auth"])

# ── Core business modules ──────────────────────────────
app.include_router(dashboard.router,        prefix="/api/v1", tags=["Dashboard"])
app.include_router(akun.router,             prefix="/api/v1", tags=["Akun"])
app.include_router(pelanggan.router,        prefix="/api/v1", tags=["Pelanggan"])
app.include_router(servis.router,           prefix="/api/v1", tags=["Servis"])
app.include_router(produk.router,           prefix="/api/v1", tags=["Produk"])
app.include_router(pos.router,              prefix="/api/v1", tags=["POS"])
app.include_router(transaksi_produk.router, prefix="/api/v1", tags=["Transaksi Produk"])
app.include_router(transfer_stok.router,    prefix="/api/v1", tags=["Transfer Stok"])

# ── Management ─────────────────────────────────────────
app.include_router(manajemen.router,   prefix="/api/v1", tags=["Manajemen"])
app.include_router(karyawan.router,    prefix="/api/v1", tags=["Karyawan"])
app.include_router(target.router,      prefix="/api/v1", tags=["Target"])
app.include_router(investor.router,    prefix="/api/v1", tags=["Investor"])

# ── Laporan ────────────────────────────────────────────
app.include_router(laporan.router,     prefix="/api/v1", tags=["Laporan"])

# ── Master data & Settings ─────────────────────────────
app.include_router(master.router,      prefix="/api/v1", tags=["Master Data"])
app.include_router(pengaturan.router,  prefix="/api/v1", tags=["Pengaturan"])

# ── Utility ────────────────────────────────────────────
app.include_router(keranjang.router,   prefix="/api/v1", tags=["Keranjang"])
app.include_router(router_arsip,       prefix="/api/v1", tags=["Arsip & Audit Log"])


# ── Health checks ──────────────────────────────────────
@app.get("/", tags=["Health"])
async def root():
    return {
        "app": "SarabaBisa Premium API",
        "version": "2.0.0",
        "status": "running",
        "docs": "/api/docs",
    }


@app.get("/health", tags=["Health"])
async def health():
    from app.core.cache import get_redis
    try:
        redis = await get_redis()
        await redis.ping()
        redis_status = "ok"
    except Exception:
        redis_status = "error"

    return {
        "status": "ok",
        "redis": redis_status,
        "timestamp": __import__("datetime").datetime.utcnow().isoformat(),
    }
