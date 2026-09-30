"""
Master Data API — merek, model, kapasitas, warna, tipe OS,
jenis barang, metode pembayaran, kategori produk, sub-kategori, supplier
Semua di-cache karena jarang berubah.
"""
from fastapi import APIRouter, Depends, Query, HTTPException
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func, and_, update
from typing import Optional
from datetime import datetime

from app.core.database import get_db
from app.core.security import get_current_user, require_any, require_admin_toko
from app.core.cache import cache_get, cache_set, cache_delete_pattern, CacheTTL, CacheKeys
from app.models.models import (
    Merek, ModelSeri, Kapasitas, Warna, TipeOs,
    JenisBarang, MetodePembayaran, KategoriProduk,
    SubKategoriProduk, Supplier, TindakanServis, Sistem, SyaratKetentuan
)
from app.utils.query import paginate

router = APIRouter()


# ── Generic helper ──
async def _list_master(db, model, cabang_id, page=1, per_page=50, search=None, extra_cols=None):
    cols = [model.id, model.nama]
    if extra_cols:
        cols.extend(extra_cols)
    base = and_(model.cabang_id == cabang_id)
    q = select(*cols).where(base).order_by(model.nama.asc())
    cq = select(func.count(model.id)).where(base)
    if search:
        t = f"%{search}%"
        q = q.where(model.nama.ilike(t))
        cq = cq.where(model.nama.ilike(t))
    return await paginate(db, q, cq, page, per_page)


# ─── Merek ───────────────────────────────────────────────
@router.get("/master/merek")
@router.get("/master/master-merek")
async def list_merek(
    page: int = Query(1), per_page: int = Query(50),
    search: Optional[str] = None,
    db: AsyncSession = Depends(get_db), current_user=Depends(require_any),
):
    return await _list_master(db, Merek, current_user.cabang_id, page, per_page, search)


@router.post("/master/master-merek", status_code=201)
async def create_merek(body: dict, db: AsyncSession = Depends(get_db), current_user=Depends(require_admin_toko)):
    m = Merek(nama=body["nama"], cabang_id=current_user.cabang_id)
    db.add(m)
    await db.flush()
    await cache_delete_pattern(f"master:merek:{current_user.cabang_id}")
    return {"id": m.id}


@router.put("/master/master-merek/{id}")
async def update_merek(id: int, body: dict, db: AsyncSession = Depends(get_db), current_user=Depends(require_admin_toko)):
    await db.execute(update(Merek).where(Merek.id == id, Merek.cabang_id == current_user.cabang_id).values(nama=body["nama"]))
    await cache_delete_pattern(f"master:merek:{current_user.cabang_id}")
    return {"message": "Merek diperbarui"}


@router.delete("/master/master-merek/{id}")
async def delete_merek(id: int, db: AsyncSession = Depends(get_db), current_user=Depends(require_admin_toko)):
    await db.execute(update(Merek).where(Merek.id == id).values(deleted_at=datetime.utcnow()) if hasattr(Merek, 'deleted_at') else update(Merek).where(Merek.id == id))
    return {"message": "Merek dihapus"}


# ─── Model Seri ──────────────────────────────────────────
@router.get("/master/model-seri")
@router.get("/master/master-model-seri")
async def list_model_seri(
    merek_id: Optional[int] = None,
    page: int = Query(1), per_page: int = Query(100),
    db: AsyncSession = Depends(get_db), current_user=Depends(require_any),
):
    q = (
        select(ModelSeri.id, ModelSeri.nama, ModelSeri.merek_id, Merek.nama.label("merek_nama"))
        .outerjoin(Merek, Merek.id == ModelSeri.merek_id)
        .where(ModelSeri.cabang_id == current_user.cabang_id)
        .order_by(ModelSeri.nama.asc())
    )
    cq = select(func.count(ModelSeri.id)).where(ModelSeri.cabang_id == current_user.cabang_id)
    if merek_id:
        q = q.where(ModelSeri.merek_id == merek_id)
        cq = cq.where(ModelSeri.merek_id == merek_id)
    return await paginate(db, q, cq, page, per_page)


@router.post("/master/master-model-seri", status_code=201)
async def create_model_seri(body: dict, db: AsyncSession = Depends(get_db), current_user=Depends(require_admin_toko)):
    m = ModelSeri(nama=body["nama"], merek_id=body.get("merek_id"), cabang_id=current_user.cabang_id)
    db.add(m)
    await db.flush()
    await cache_delete_pattern(f"master:model:{current_user.cabang_id}")
    return {"id": m.id}


# ─── Kapasitas, Warna, Tipe OS, Jenis Barang ─────────────
@router.get("/master/kapasitas")
@router.get("/master/master-kapasitas")
async def list_kapasitas(db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    r = await db.execute(select(Kapasitas.id, Kapasitas.nama).where(Kapasitas.cabang_id == cu.cabang_id).order_by(Kapasitas.nama))
    return r.mappings().all()


@router.post("/master/kapasitas", status_code=201)
@router.post("/master/master-kapasitas", status_code=201)
async def create_kapasitas(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_admin_toko)):
    m = Kapasitas(nama=body["nama"], cabang_id=cu.cabang_id)
    db.add(m)
    await db.flush()
    return {"id": m.id}


@router.get("/master/warna")
@router.get("/master/master-warna")
async def list_warna(db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    r = await db.execute(select(Warna.id, Warna.nama).where(Warna.cabang_id == cu.cabang_id).order_by(Warna.nama))
    return r.mappings().all()


@router.post("/master/warna", status_code=201)
@router.post("/master/master-warna", status_code=201)
async def create_warna(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_admin_toko)):
    m = Warna(nama=body["nama"], cabang_id=cu.cabang_id)
    db.add(m)
    await db.flush()
    return {"id": m.id}


@router.get("/master/tipe-os")
@router.get("/master/master-tipe-os")
async def list_tipe_os(db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    r = await db.execute(select(TipeOs.id, TipeOs.nama).where(TipeOs.cabang_id == cu.cabang_id).order_by(TipeOs.nama))
    return r.mappings().all()


@router.get("/master/cabang")
async def list_master_cabang(db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    from app.models.models import Cabang
    r = await db.execute(select(Cabang.id, Cabang.nama, Cabang.alamat, Cabang.nomor_hp).where(Cabang.deleted_at.is_(None)))
    return r.mappings().all()


@router.get("/master/jenis-barang")
async def list_jenis_barang(db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    r = await db.execute(select(JenisBarang.id, JenisBarang.nama).where(JenisBarang.cabang_id == cu.cabang_id).order_by(JenisBarang.nama))
    return r.mappings().all()


@router.post("/master/jenis-barang", status_code=201)
async def create_jenis_barang(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_admin_toko)):
    m = JenisBarang(nama=body["nama"], cabang_id=cu.cabang_id)
    db.add(m)
    await db.flush()
    return {"id": m.id}


# ─── Metode Pembayaran ────────────────────────────────────
@router.get("/master/metode-pembayaran")
async def list_metode_pembayaran(db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    r = await db.execute(select(MetodePembayaran.id, MetodePembayaran.nama).where(MetodePembayaran.cabang_id == cu.cabang_id))
    return r.mappings().all()


# ─── Kategori & Sub-Kategori Produk ───────────────────────
@router.get("/master/kategori-produk")
async def list_kategori(
    db: AsyncSession = Depends(get_db), cu=Depends(require_any),
    page: int = Query(1), per_page: int = Query(50),
):
    base = KategoriProduk.cabang_id == cu.cabang_id
    q = select(KategoriProduk.id, KategoriProduk.nama).where(base).order_by(KategoriProduk.nama)
    cq = select(func.count(KategoriProduk.id)).where(base)
    return await paginate(db, q, cq, page, per_page)


@router.get("/master/sub-kategori-produk")
async def list_sub_kategori(
    kategori_id: Optional[int] = None,
    db: AsyncSession = Depends(get_db), cu=Depends(require_any),
):
    q = (
        select(SubKategoriProduk.id, SubKategoriProduk.nama, SubKategoriProduk.kategori_id,
               KategoriProduk.nama.label("kategori_nama"))
        .outerjoin(KategoriProduk, KategoriProduk.id == SubKategoriProduk.kategori_id)
        .where(SubKategoriProduk.cabang_id == cu.cabang_id)
        .order_by(SubKategoriProduk.nama)
    )
    if kategori_id:
        q = q.where(SubKategoriProduk.kategori_id == kategori_id)
    r = await db.execute(q)
    return r.mappings().all()


# ─── Supplier ─────────────────────────────────────────────
@router.get("/master/supplier")
async def list_supplier(
    page: int = Query(1), per_page: int = Query(15),
    search: Optional[str] = None,
    db: AsyncSession = Depends(get_db), cu=Depends(require_any),
):
    base = and_(Supplier.cabang_id == cu.cabang_id, Supplier.deleted_at.is_(None))
    q = select(Supplier.id, Supplier.nama, Supplier.no_hp, Supplier.email).where(base).order_by(Supplier.nama)
    cq = select(func.count(Supplier.id)).where(base)
    if search:
        t = f"%{search}%"
        q = q.where(Supplier.nama.ilike(t))
        cq = cq.where(Supplier.nama.ilike(t))
    return await paginate(db, q, cq, page, per_page)


@router.post("/master/supplier", status_code=201)
async def create_supplier(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_admin_toko)):
    s = Supplier(nama=body["nama"], no_hp=body.get("no_hp"), email=body.get("email"),
                  alamat=body.get("alamat"), cabang_id=cu.cabang_id)
    db.add(s)
    await db.flush()
    return {"id": s.id}


# ─── Absensi Master ────────────────────────────────────────
@router.get("/master/master-absensi")
async def master_absensi_config(db: AsyncSession = Depends(get_db), cu=Depends(require_admin_toko)):
    """Konfigurasi absensi — jam masuk/keluar default."""
    r = await db.execute(
        select(Sistem).where(Sistem.cabang_id == cu.cabang_id)
    )
    sistem = r.scalar_one_or_none()
    return {
        "jam_buka": str(sistem.jam_buka) if sistem else "08:00:00",
        "jam_tutup": str(sistem.jam_tutup) if sistem else "17:00:00",
        "hari_kerja": sistem.hari_kerja if sistem else "Senin,Selasa,Rabu,Kamis,Jumat",
    }
