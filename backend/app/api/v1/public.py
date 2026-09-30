"""
Public Endpoints — Tracking Servis & Cek Garansi (tanpa login)
Arsip Data — backup database, audit log, restore
"""
from fastapi import APIRouter, Depends, Query, HTTPException, BackgroundTasks
from fastapi.responses import FileResponse, StreamingResponse
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func, and_, text
from typing import Optional
from datetime import datetime, date
import os, json

from app.core.database import get_db
from app.core.security import require_kepala_toko
from app.models.models import TransaksiServis, HistoryGaransi, Pelanggan, AuditLog

router_public = APIRouter()
router_arsip = APIRouter()


# ─── PUBLIC: Tracking Servis ───────────────────────────────
@router_public.get("/tracking")
async def tracking_servis(
    no_nota: str = Query(..., description="Nomor nota servis"),
    db: AsyncSession = Depends(get_db),
):
    """
    Public tracking — tanpa login.
    Pelanggan bisa track status HP-nya lewat no nota.
    """
    r = await db.execute(
        select(
            TransaksiServis.no_nota,
            TransaksiServis.status,
            TransaksiServis.nama_barang,
            TransaksiServis.kerusakan,
            TransaksiServis.estimasi_pengerjaan,
            TransaksiServis.estimasi_biaya,
            TransaksiServis.tgl_masuk,
            TransaksiServis.tgl_selesai,
            TransaksiServis.catatan_teknisi,
            Pelanggan.nama.label("nama_pelanggan"),
        )
        .outerjoin(Pelanggan, Pelanggan.id == TransaksiServis.pelanggan_id)
        .where(
            TransaksiServis.no_nota == no_nota,
            TransaksiServis.deleted_at.is_(None),
        )
    )
    data = r.mappings().first()
    if not data:
        raise HTTPException(404, "No nota tidak ditemukan")

    return {
        "no_nota": data["no_nota"],
        "nama_pelanggan": data["nama_pelanggan"],
        "nama_barang": data["nama_barang"],
        "kerusakan": data["kerusakan"],
        "status": data["status"],
        "estimasi_pengerjaan": data["estimasi_pengerjaan"],
        "estimasi_biaya": float(data["estimasi_biaya"] or 0),
        "tgl_masuk": data["tgl_masuk"],
        "tgl_selesai": data["tgl_selesai"],
        "catatan_teknisi": data["catatan_teknisi"],
    }


# ─── PUBLIC: Cek Garansi ───────────────────────────────────
@router_public.get("/garansi")
async def cek_garansi(
    no_nota: str = Query(...),
    db: AsyncSession = Depends(get_db),
):
    """Cek status garansi berdasarkan no nota servis asal."""
    r = await db.execute(
        select(
            HistoryGaransi.id,
            HistoryGaransi.no_nota_garansi,
            HistoryGaransi.tgl_garansi,
            HistoryGaransi.expired_garansi,
            HistoryGaransi.keluhan,
            HistoryGaransi.status,
            TransaksiServis.no_nota.label("no_servis_asal"),
            TransaksiServis.nama_barang,
            Pelanggan.nama.label("nama_pelanggan"),
        )
        .join(TransaksiServis, TransaksiServis.id == HistoryGaransi.servis_id)
        .outerjoin(Pelanggan, Pelanggan.id == TransaksiServis.pelanggan_id)
        .where(
            (TransaksiServis.no_nota == no_nota) | (HistoryGaransi.no_nota_garansi == no_nota),
            HistoryGaransi.deleted_at.is_(None),
        )
        .order_by(HistoryGaransi.id.desc())
        .limit(5)
    )
    data = r.mappings().all()
    if not data:
        raise HTTPException(404, "Data garansi tidak ditemukan")

    return [dict(d) for d in data]


# ─── ARSIP: Backup Database ────────────────────────────────
@router_arsip.post("/data-management/backup")
async def backup_database(
    background_tasks: BackgroundTasks,
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_kepala_toko),
):
    """
    Export database ke JSON.
    Dikerjakan di background — file tersimpan di storage/backups/.
    """
    import asyncio

    backup_file = f"storage/backups/backup_{cu.cabang_id}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
    os.makedirs("storage/backups", exist_ok=True)

    async def do_backup():
        # Export semua tabel utama ke JSON
        tables = [
            "transaksi_servis", "orders", "order_details",
            "pelanggans", "produks", "expenses", "kasbons",
        ]
        result = {}
        for table in tables:
            try:
                r = await db.execute(text(f"SELECT * FROM {table} WHERE cabang_id = :cid"), {"cid": cu.cabang_id})
                rows = [dict(row._mapping) for row in r]
                # Convert datetime to str
                for row in rows:
                    for k, v in row.items():
                        if isinstance(v, (datetime, date)):
                            row[k] = str(v)
                result[table] = rows
            except Exception as e:
                result[table] = {"error": str(e)}

        with open(backup_file, "w", encoding="utf-8") as f:
            json.dump(result, f, ensure_ascii=False, indent=2)

    background_tasks.add_task(do_backup)
    return {"message": "Backup sedang diproses", "file": backup_file}


@router_arsip.get("/arsip-data")
async def list_arsip(
    db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko),
):
    """List semua file backup tersedia."""
    backup_dir = "storage/backups"
    os.makedirs(backup_dir, exist_ok=True)
    files = []
    for fname in os.listdir(backup_dir):
        if fname.endswith(".json") or fname.endswith(".sql"):
            fpath = os.path.join(backup_dir, fname)
            stat = os.stat(fpath)
            files.append({
                "nama": fname,
                "ukuran_kb": round(stat.st_size / 1024, 1),
                "tgl_buat": datetime.fromtimestamp(stat.st_ctime).isoformat(),
            })
    files.sort(key=lambda x: x["tgl_buat"], reverse=True)
    return files


@router_arsip.get("/arsip-data/{nama}/download")
async def download_arsip(nama: str, cu=Depends(require_kepala_toko)):
    fpath = os.path.join("storage/backups", nama)
    if not os.path.exists(fpath):
        raise HTTPException(404, "File tidak ditemukan")
    return FileResponse(fpath, filename=nama, media_type="application/octet-stream")


@router_arsip.delete("/arsip-data/{nama}")
async def delete_arsip(nama: str, cu=Depends(require_kepala_toko)):
    fpath = os.path.join("storage/backups", nama)
    if os.path.exists(fpath):
        os.remove(fpath)
    return {"message": "File dihapus"}


# ─── AUDIT LOG ────────────────────────────────────────────
@router_arsip.get("/audit-log")
async def list_audit_log(
    page: int = Query(1), per_page: int = Query(20),
    tipe: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko),
):
    base = and_(AuditLog.cabang_id == cu.cabang_id)
    q = select(
        AuditLog.id, AuditLog.user_id, AuditLog.tipe, AuditLog.aksi,
        AuditLog.keterangan, AuditLog.created_at,
    ).where(base).order_by(AuditLog.created_at.desc())
    cq = select(func.count(AuditLog.id)).where(base)

    if tipe:
        q = q.where(AuditLog.tipe == tipe)
        cq = cq.where(AuditLog.tipe == tipe)

    offset = (page - 1) * per_page
    r = await db.execute(q.offset(offset).limit(per_page))
    count_r = await db.execute(cq)
    total = count_r.scalar_one()

    return {
        "data": r.mappings().all(),
        "meta": {"total": total, "page": page, "per_page": per_page, "last_page": -(-total // per_page)},
    }
