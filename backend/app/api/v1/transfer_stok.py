"""
Transfer Stok API — antar cabang, dengan approval.
Multi-cabang: source cabang dan destination cabang berbeda.
"""
from fastapi import APIRouter, Depends, Query, HTTPException
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func, and_, update
from typing import Optional
from datetime import datetime

from app.core.database import get_db
from app.core.security import require_kepala_toko, require_any
from app.models.models import TransferStok, TransferStokDetail, Produk, Cabang, User
from app.utils.query import paginate
from app.utils.nomor import generate_no_nota

router = APIRouter()


@router.get("/transfer-stok")
async def list_transfer(
    page: int = Query(1), per_page: int = Query(15),
    status: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko),
):
    base = and_(
        TransferStok.deleted_at.is_(None),
        (TransferStok.cabang_asal == cu.cabang_id) | (TransferStok.cabang_tujuan == cu.cabang_id),
    )
    q = select(
        TransferStok.id, TransferStok.no_transfer, TransferStok.cabang_asal,
        TransferStok.cabang_tujuan, TransferStok.status, TransferStok.catatan,
        TransferStok.created_at, User.nama.label("user_nama"),
        Cabang.nama.label("tujuan_nama"),
    ).outerjoin(User, User.id == TransferStok.user_id).outerjoin(Cabang, Cabang.id == TransferStok.cabang_tujuan).where(base).order_by(TransferStok.created_at.desc())
    cq = select(func.count(TransferStok.id)).where(base)

    if status:
        q = q.where(TransferStok.status == status)
        cq = cq.where(TransferStok.status == status)

    return await paginate(db, q, cq, page, per_page)


@router.get("/transfer-stok/{id}")
async def get_transfer(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    from sqlalchemy.orm import selectinload
    r = await db.execute(
        select(TransferStok).options(selectinload(TransferStok.details))
        .where(TransferStok.id == id)
    )
    t = r.scalar_one_or_none()
    if not t:
        raise HTTPException(404, "Transfer stok tidak ditemukan")
    return {
        "id": t.id, "no_transfer": t.no_transfer, "cabang_asal": t.cabang_asal,
        "cabang_tujuan": t.cabang_tujuan, "status": t.status, "catatan": t.catatan,
        "created_at": t.created_at,
        "details": [{"produk_id": d.produk_id, "nama_produk": d.nama_produk, "qty": d.qty} for d in t.details],
    }


@router.post("/transfer-stok", status_code=201)
async def create_transfer(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    """
    Buat transfer stok baru.
    Stok belum dikurangi/ditambah sampai approve.
    """
    no_transfer = await generate_no_nota(db, cu.cabang_id, "TRF")
    t = TransferStok(
        no_transfer=no_transfer,
        cabang_asal=cu.cabang_id,
        cabang_tujuan=body["cabang_tujuan"],
        user_id=cu.id,
        catatan=body.get("catatan"),
        status="menunggu",
    )
    db.add(t)
    await db.flush()

    for item in body.get("items", []):
        # Validasi stok di cabang asal
        r = await db.execute(
            select(Produk).where(Produk.id == item["produk_id"], Produk.cabang_id == cu.cabang_id)
        )
        produk = r.scalar_one_or_none()
        if not produk:
            raise HTTPException(404, f"Produk ID {item['produk_id']} tidak ditemukan di cabang ini")
        if produk.stok < item["qty"]:
            raise HTTPException(400, f"Stok {produk.nama} tidak mencukupi (tersedia: {produk.stok})")

        db.add(TransferStokDetail(
            transfer_id=t.id,
            produk_id=item["produk_id"],
            nama_produk=produk.nama,
            qty=item["qty"],
        ))

    return {"id": t.id, "no_transfer": no_transfer}


@router.post("/transfer-stok/{id}/approve")
async def approve_transfer(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    """
    Approve transfer: kurangi stok di cabang asal, tambah di cabang tujuan.
    Pastikan cabang tujuan sudah punya record produk yang sama.
    """
    r = await db.execute(select(TransferStok).where(TransferStok.id == id))
    t = r.scalar_one_or_none()
    if not t:
        raise HTTPException(404, "Transfer tidak ditemukan")
    if t.status != "menunggu":
        raise HTTPException(400, "Transfer ini sudah diproses")
    if t.cabang_tujuan != cu.cabang_id and t.cabang_asal != cu.cabang_id:
        raise HTTPException(403, "Bukan transfer untuk cabang Anda")

    # Load details
    r_d = await db.execute(select(TransferStokDetail).where(TransferStokDetail.transfer_id == id))
    details = r_d.scalars().all()

    for d in details:
        # Kurangi stok asal
        await db.execute(
            update(Produk).where(Produk.id == d.produk_id, Produk.cabang_id == t.cabang_asal)
            .values(stok=Produk.stok - d.qty)
        )

        # Tambah stok tujuan — cari produk yang sama di cabang tujuan
        r_tujuan = await db.execute(
            select(Produk.id).where(Produk.cabang_id == t.cabang_tujuan, Produk.kode == (
                select(Produk.kode).where(Produk.id == d.produk_id).scalar_subquery()
            ))
        )
        produk_tujuan_id = r_tujuan.scalar_one_or_none()
        if produk_tujuan_id:
            await db.execute(
                update(Produk).where(Produk.id == produk_tujuan_id)
                .values(stok=Produk.stok + d.qty)
            )

    await db.execute(
        update(TransferStok).where(TransferStok.id == id)
        .values(status="selesai", approved_by=cu.id, approved_at=datetime.utcnow())
    )
    return {"message": "Transfer stok disetujui"}


@router.delete("/transfer-stok/{id}")
async def delete_transfer(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    await db.execute(
        update(TransferStok).where(TransferStok.id == id, TransferStok.status == "menunggu")
        .values(deleted_at=datetime.utcnow())
    )
    return {"message": "Transfer dihapus"}


@router.get("/transfer-stok/produk-by-cabang/{cabang_id}/{kategori}")
async def produk_by_cabang(
    cabang_id: int, kategori: str,
    db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko),
):
    """Ambil produk stok di cabang tertentu untuk form transfer."""
    r = await db.execute(
        select(Produk.id, Produk.nama, Produk.kode, Produk.stok, Produk.tipe)
        .where(
            Produk.cabang_id == cabang_id,
            Produk.tipe == kategori,
            Produk.stok > 0,
            Produk.deleted_at.is_(None),
        ).order_by(Produk.nama)
    )
    return r.mappings().all()
