"""
Transaksi Produk API — Penjualan (list/edit/approve), Purchase, Retur, Tukar Tambah, QC
Multi-cabang + notifikasi Telegram + setting pajak.
"""
from fastapi import APIRouter, Depends, Query, HTTPException
from fastapi.responses import StreamingResponse
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func, and_, update
from typing import Optional
from datetime import datetime

from app.core.database import get_db
from app.core.security import require_any, require_kepala_toko, require_admin_toko
from app.core.security import check_edit_transaksi_allowed, check_approval_hapus_required
from app.models.models import (
    Order, OrderDetail, Produk, Pelanggan, User, StoreSetting,
    Purchase, PurchaseDetail, Retur, TukarTambah, TukarTambahDetail
)
from app.utils.query import paginate
from app.utils.nomor import generate_no_nota
from app.utils.notifications import notif_penjualan_baru

router = APIRouter()


# ─── TRANSAKSI PENJUALAN (dari POS) ──────────────────────
@router.get("/produk/transaksi-produk")
async def list_transaksi_produk(
    page: int = Query(1), per_page: int = Query(15),
    status: Optional[str] = Query(None),
    search: Optional[str] = Query(None),
    date_from: Optional[str] = Query(None),
    date_to: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db), cu=Depends(require_any),
):
    base = and_(Order.cabang_id == cu.cabang_id, Order.deleted_at.is_(None))
    q = select(
        Order.id, Order.no_invoice, Order.status, Order.total, Order.diskon,
        Order.dp, Order.sisa_bayar, Order.ppn, Order.is_approve, Order.created_at,
        Pelanggan.nama.label("pelanggan_nama"),
        User.nama.label("kasir_nama"),
    ).outerjoin(Pelanggan, Pelanggan.id == Order.pelanggan_id).outerjoin(User, User.id == Order.user_id).where(base).order_by(Order.created_at.desc())
    cq = select(func.count(Order.id)).where(base)

    if status:
        q = q.where(Order.status == status)
        cq = cq.where(Order.status == status)
    if search:
        t = f"%{search}%"
        q = q.where(Order.no_invoice.ilike(t) | Pelanggan.nama.ilike(t))
    if date_from:
        q = q.where(Order.created_at >= date_from)
    if date_to:
        q = q.where(Order.created_at <= date_to)

    return await paginate(db, q, cq, page, per_page)


@router.get("/produk/transaksi-produk/summary/counts")
async def get_transaksi_counts(db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    base = and_(Order.cabang_id == cu.cabang_id, Order.deleted_at.is_(None))
    c_semua = await db.scalar(select(func.count(Order.id)).where(base))
    c_lunas = await db.scalar(select(func.count(Order.id)).where(base, Order.status == "lunas"))
    c_belum_lunas = await db.scalar(select(func.count(Order.id)).where(base, Order.status == "belum_lunas"))
    return {
        "semua": c_semua or 0,
        "lunas": c_lunas or 0,
        "belum_lunas": c_belum_lunas or 0,
    }


@router.get("/produk/transaksi-produk/{id}")
async def get_transaksi_produk(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    from sqlalchemy.orm import selectinload
    r = await db.execute(
        select(Order).options(selectinload(Order.details), selectinload(Order.pelanggan))
        .where(Order.id == id, Order.cabang_id == cu.cabang_id)
    )
    o = r.scalar_one_or_none()
    if not o:
        raise HTTPException(404, "Transaksi tidak ditemukan")
    return {
        "id": o.id, "no_invoice": o.no_invoice, "status": o.status,
        "total": float(o.total or 0), "diskon": float(o.diskon or 0),
        "ppn": float(o.ppn or 0), "dp": float(o.dp or 0), "sisa_bayar": float(o.sisa_bayar or 0),
        "is_approve": o.is_approve, "catatan": o.catatan, "created_at": o.created_at,
        "pelanggan": {"id": o.pelanggan.id, "nama": o.pelanggan.nama} if o.pelanggan else None,
        "items": [{"nama_produk": d.nama_produk, "qty": d.qty, "harga": float(d.harga or 0), "total": float(d.total or 0)} for d in o.details],
    }


@router.put("/produk/transaksi-produk/{id}")
async def update_transaksi_produk(id: int, body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    r = await db.execute(select(Order).where(Order.id == id, Order.cabang_id == cu.cabang_id))
    o = r.scalar_one_or_none()
    if not o:
        raise HTTPException(404, "Transaksi tidak ditemukan")

    # Cek permission edit
    allowed = await check_edit_transaksi_allowed(cu, db, o.created_at)
    if not allowed:
        raise HTTPException(403, "Edit transaksi hari lalu tidak diizinkan. Hubungi Kepala Toko.")

    await db.execute(
        update(Order).where(Order.id == id).values(
            pelanggan_id=body.get("pelanggan_id"),
            catatan=body.get("catatan"),
            metode_pembayaran_id=body.get("metode_pembayaran_id"),
            updated_at=datetime.utcnow(),
        )
    )
    return {"message": "Transaksi diperbarui"}


@router.delete("/produk/transaksi-produk/{id}")
async def delete_transaksi_produk(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    """Soft delete dengan cek approval setting per cabang."""
    from app.models.models import PersetujuanHapusTransaksi
    needs_approval = await check_approval_hapus_required(db, cu.cabang_id)

    if needs_approval and not cu.is_kepala_toko:
        # Buat request approval
        r = await db.execute(select(Order).where(Order.id == id))
        o = r.scalar_one_or_none()
        if not o:
            raise HTTPException(404, "Tidak ditemukan")
        db.add(PersetujuanHapusTransaksi(
            tipe="penjualan", record_id=id, user_id=cu.id,
            cabang_id=cu.cabang_id, status="menunggu",
        ))
        return {"message": "Permintaan hapus diajukan ke Kepala Toko"}

    await db.execute(update(Order).where(Order.id == id).values(deleted_at=datetime.utcnow()))
    return {"message": "Transaksi dihapus"}


@router.post("/produk/transaksi-produk/delete-batch")
async def delete_transaksi_batch(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    await db.execute(update(Order).where(Order.id.in_(body.get("ids", [])), Order.cabang_id == cu.cabang_id).values(deleted_at=datetime.utcnow()))
    return {"message": "Batch dihapus"}


@router.patch("/produk/transaksi-produk/batch/lunas")
async def batch_lunas(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    await db.execute(update(Order).where(Order.id.in_(body.get("ids", [])), Order.cabang_id == cu.cabang_id).values(status="lunas", sisa_bayar=0))
    return {"message": "Batch tandai lunas"}


@router.patch("/produk/transaksi-produk/batch/approve")
async def batch_approve_penjualan(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    await db.execute(update(Order).where(Order.id.in_(body.get("ids", [])), Order.cabang_id == cu.cabang_id).values(is_approve="Setuju", tgl_disetujui=datetime.utcnow()))
    return {"message": "Batch disetujui"}


@router.patch("/produk/transaksi-produk/batch/reject")
async def batch_reject_penjualan(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    await db.execute(update(Order).where(Order.id.in_(body.get("ids", [])), Order.cabang_id == cu.cabang_id).values(is_approve="Ditolak"))
    return {"message": "Batch ditolak"}


# ─── PERSETUJUAN HAPUS TRANSAKSI ─────────────────────────
@router.get("/persetujuan-hapus-transaksi")
async def list_persetujuan_hapus(db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    from app.models.models import PersetujuanHapusTransaksi
    r = await db.execute(
        select(PersetujuanHapusTransaksi)
        .where(PersetujuanHapusTransaksi.cabang_id == cu.cabang_id, PersetujuanHapusTransaksi.status == "menunggu")
        .order_by(PersetujuanHapusTransaksi.created_at.desc())
    )
    return r.scalars().all()


@router.post("/persetujuan-hapus-transaksi/{id}/approve")
async def approve_hapus(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    from app.models.models import PersetujuanHapusTransaksi
    r = await db.execute(select(PersetujuanHapusTransaksi).where(PersetujuanHapusTransaksi.id == id))
    req = r.scalar_one_or_none()
    if not req:
        raise HTTPException(404, "Permintaan tidak ditemukan")

    # Hapus record yang diminta
    model_map = {"servis": TransaksiServis, "penjualan": Order}
    Model = model_map.get(req.tipe)
    if Model:
        await db.execute(update(Model).where(Model.id == req.record_id).values(deleted_at=datetime.utcnow()))

    await db.execute(update(PersetujuanHapusTransaksi).where(PersetujuanHapusTransaksi.id == id).values(status="disetujui"))
    return {"message": "Permintaan hapus disetujui"}


@router.post("/persetujuan-hapus-transaksi/{id}/reject")
async def reject_hapus(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    from app.models.models import PersetujuanHapusTransaksi
    await db.execute(update(PersetujuanHapusTransaksi).where(PersetujuanHapusTransaksi.id == id).values(status="ditolak"))
    return {"message": "Permintaan ditolak"}


# ─── PURCHASE (PEMBELIAN) ─────────────────────────────────
@router.get("/produk/purchase")
async def list_purchase(
    page: int = Query(1), per_page: int = Query(15),
    db: AsyncSession = Depends(get_db), cu=Depends(require_any),
):
    from app.models.models import Supplier
    base = and_(Purchase.cabang_id == cu.cabang_id, Purchase.deleted_at.is_(None))
    q = select(
        Purchase.id, Purchase.no_po, Purchase.status, Purchase.total,
        Purchase.tgl_po, Purchase.tgl_terima, Purchase.created_at,
        Supplier.nama.label("supplier_nama"),
    ).outerjoin(Supplier, Supplier.id == Purchase.supplier_id).where(base).order_by(Purchase.created_at.desc())
    cq = select(func.count(Purchase.id)).where(base)
    return await paginate(db, q, cq, page, per_page)


@router.get("/produk/purchase/{id}")
async def get_purchase(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    from sqlalchemy.orm import selectinload
    r = await db.execute(
        select(Purchase).options(selectinload(Purchase.details))
        .where(Purchase.id == id, Purchase.cabang_id == cu.cabang_id)
    )
    p = r.scalar_one_or_none()
    if not p:
        raise HTTPException(404, "Purchase tidak ditemukan")
    return {
        "id": p.id, "no_po": p.no_po, "status": p.status, "total": float(p.total or 0),
        "tgl_po": p.tgl_po, "tgl_terima": p.tgl_terima, "catatan": p.catatan,
        "details": [{"produk_id": d.produk_id, "nama_produk": d.nama_produk, "qty": d.qty, "harga": float(d.harga or 0)} for d in p.details],
    }


@router.post("/produk/purchase", status_code=201)
async def create_purchase(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_admin_toko)):
    no_po = await generate_no_nota(db, cu.cabang_id, "PO")
    total = sum(float(item.get("harga", 0)) * int(item.get("qty", 0)) for item in body.get("items", []))

    po = Purchase(
        no_po=no_po, cabang_id=cu.cabang_id,
        supplier_id=body.get("supplier_id"),
        user_id=cu.id, total=total,
        tgl_po=body.get("tgl_po", datetime.now().date()),
        catatan=body.get("catatan"), status="menunggu",
    )
    db.add(po)
    await db.flush()

    for item in body.get("items", []):
        db.add(PurchaseDetail(
            purchase_id=po.id,
            produk_id=item.get("produk_id"),
            nama_produk=item.get("nama_produk"),
            qty=item["qty"],
            harga=item.get("harga", 0),
        ))

    return {"id": po.id, "no_po": no_po}


@router.put("/produk/purchase/{id}")
async def update_purchase(id: int, body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_admin_toko)):
    await db.execute(
        update(Purchase).where(Purchase.id == id, Purchase.cabang_id == cu.cabang_id)
        .values(status=body.get("status"), tgl_terima=body.get("tgl_terima"), catatan=body.get("catatan"), updated_at=datetime.utcnow())
    )
    # Jika status jadi "diterima", update stok produk
    if body.get("status") == "diterima":
        r_d = await db.execute(select(PurchaseDetail).where(PurchaseDetail.purchase_id == id))
        details = r_d.scalars().all()
        for d in details:
            if d.produk_id:
                await db.execute(update(Produk).where(Produk.id == d.produk_id, Produk.cabang_id == cu.cabang_id).values(stok=Produk.stok + d.qty))
    return {"message": "Purchase diperbarui"}


@router.delete("/produk/purchase/{id}")
async def delete_purchase(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_admin_toko)):
    await db.execute(update(Purchase).where(Purchase.id == id, Purchase.cabang_id == cu.cabang_id).values(deleted_at=datetime.utcnow()))
    return {"message": "Purchase dihapus"}


# ─── RETUR ───────────────────────────────────────────────
@router.get("/produk/retur")
async def list_retur(
    page: int = Query(1), per_page: int = Query(15),
    db: AsyncSession = Depends(get_db), cu=Depends(require_any),
):
    base = and_(Retur.cabang_id == cu.cabang_id, Retur.deleted_at.is_(None))
    q = select(Retur.id, Retur.no_retur, Retur.alasan, Retur.status, Retur.created_at,
               Pelanggan.nama.label("pelanggan_nama"),
               ).outerjoin(Pelanggan, Pelanggan.id == Retur.pelanggan_id).where(base).order_by(Retur.created_at.desc())
    cq = select(func.count(Retur.id)).where(base)
    return await paginate(db, q, cq, page, per_page)


@router.post("/produk/retur", status_code=201)
async def create_retur(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    no_retur = await generate_no_nota(db, cu.cabang_id, "RTR")
    r = Retur(
        no_retur=no_retur, cabang_id=cu.cabang_id,
        order_id=body.get("order_id"),
        pelanggan_id=body.get("pelanggan_id"),
        alasan=body.get("alasan"), status="menunggu",
    )
    db.add(r)
    await db.flush()
    return {"id": r.id, "no_retur": no_retur}


@router.delete("/produk/retur/{id}")
async def delete_retur(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    await db.execute(update(Retur).where(Retur.id == id, Retur.cabang_id == cu.cabang_id).values(deleted_at=datetime.utcnow()))
    return {"message": "Retur dihapus"}


# ─── TUKAR TAMBAH ─────────────────────────────────────────
@router.get("/produk/tukar-tambah")
async def list_tukar_tambah(
    page: int = Query(1), per_page: int = Query(15),
    db: AsyncSession = Depends(get_db), cu=Depends(require_any),
):
    base = and_(TukarTambah.cabang_id == cu.cabang_id, TukarTambah.deleted_at.is_(None))
    q = select(
        TukarTambah.id, TukarTambah.no_tt, TukarTambah.total_beli, TukarTambah.harga_tukar,
        TukarTambah.selisih, TukarTambah.status, TukarTambah.created_at,
        Pelanggan.nama.label("pelanggan_nama"),
    ).outerjoin(Pelanggan, Pelanggan.id == TukarTambah.pelanggan_id).where(base).order_by(TukarTambah.created_at.desc())
    cq = select(func.count(TukarTambah.id)).where(base)
    return await paginate(db, q, cq, page, per_page)


@router.post("/produk/tukar-tambah", status_code=201)
async def create_tukar_tambah(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    no_tt = await generate_no_nota(db, cu.cabang_id, "TT")
    harga_tukar = float(body.get("harga_tukar", 0))
    total_beli = float(body.get("total_beli", 0))
    selisih = total_beli - harga_tukar

    tt = TukarTambah(
        no_tt=no_tt, cabang_id=cu.cabang_id,
        pelanggan_id=body.get("pelanggan_id"),
        user_id=cu.id,
        nama_hp_lama=body.get("nama_hp_lama"),
        kondisi=body.get("kondisi"),
        harga_tukar=harga_tukar,
        total_beli=total_beli,
        selisih=selisih,
        status="proses",
    )
    db.add(tt)
    await db.flush()

    for item in body.get("items", []):
        db.add(TukarTambahDetail(
            tukar_tambah_id=tt.id,
            produk_id=item.get("produk_id"),
            nama_produk=item.get("nama_produk"),
            qty=item.get("qty", 1),
            harga=item.get("harga", 0),
        ))

    return {"id": tt.id, "no_tt": no_tt, "selisih": selisih}


@router.put("/produk/tukar-tambah/{id}")
async def update_tukar_tambah(id: int, body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    await db.execute(
        update(TukarTambah).where(TukarTambah.id == id, TukarTambah.cabang_id == cu.cabang_id)
        .values(status=body.get("status"), updated_at=datetime.utcnow())
    )
    return {"message": "Tukar tambah diperbarui"}


@router.delete("/produk/tukar-tambah/{id}")
async def delete_tukar_tambah(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    await db.execute(update(TukarTambah).where(TukarTambah.id == id, TukarTambah.cabang_id == cu.cabang_id).values(deleted_at=datetime.utcnow()))
    return {"message": "Tukar tambah dihapus"}


# ─── CETAK NOTA PENJUALAN PDF ─────────────────────────────
@router.get("/produk/transaksi-produk/{id}/cetak/termal")
async def cetak_penjualan_termal(
    id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_any)
):
    from sqlalchemy.orm import selectinload
    from app.utils.pdf_generator import generate_nota_penjualan_termal

    r = await db.execute(
        select(Order)
        .options(selectinload(Order.details), selectinload(Order.pelanggan), selectinload(Order.user))
        .where(Order.id == id, Order.cabang_id == cu.cabang_id)
    )
    o = r.scalar_one_or_none()
    if not o:
        raise HTTPException(404, "Transaksi tidak ditemukan")

    r_store = await db.execute(select(StoreSetting).where(StoreSetting.cabang_id == cu.cabang_id))
    setting = r_store.scalar_one_or_none()

    order_data = {
        "invoice_no": o.no_invoice,
        "tanggal": o.created_at.strftime("%d/%m/%Y %H:%M"),
        "kasir": o.user.nama if o.user else "Kasir",
        "pelanggan_nama": o.pelanggan.nama if o.pelanggan else "Umum",
        "sub_total": float(o.total or 0) + float(o.diskon or 0),
        "diskon": float(o.diskon or 0),
        "total": float(o.total or 0),
        "bayar": float(o.bayar or o.total or 0),
        "kembali": float(o.kembali or 0),
        "metode_pembayaran": getattr(o, "payment_method", "Tunai") or "Tunai",
        "items": [
            {
                "nama_produk": d.nama_produk,
                "qty": d.qty,
                "harga": float(d.harga or 0),
                "total": float(d.total or 0),
            }
            for d in o.details
        ],
    }

    store_info = {
        "nama_toko": setting.nama_toko if setting and setting.nama_toko else "SARABABISA PREMIUM",
        "alamat": setting.alamat if setting else "",
        "no_hp": setting.no_hp if setting else "",
    }

    pdf = generate_nota_penjualan_termal(order_data, store_info)
    return StreamingResponse(
        pdf, media_type="application/pdf",
        headers={"Content-Disposition": f"inline; filename=nota_penjualan_{o.no_invoice}.pdf"}
    )


@router.get("/produk/transaksi-produk/{id}/cetak/inkjet")
async def cetak_penjualan_inkjet(
    id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_any)
):
    from sqlalchemy.orm import selectinload
    from app.utils.pdf_generator import generate_nota_inkjet
    from app.models.models import SyaratKetentuan

    r = await db.execute(
        select(Order)
        .options(selectinload(Order.details), selectinload(Order.pelanggan), selectinload(Order.user))
        .where(Order.id == id, Order.cabang_id == cu.cabang_id)
    )
    o = r.scalar_one_or_none()
    if not o:
        raise HTTPException(404, "Transaksi tidak ditemukan")

    r_store = await db.execute(select(StoreSetting).where(StoreSetting.cabang_id == cu.cabang_id))
    setting = r_store.scalar_one_or_none()

    r_sk = await db.execute(select(SyaratKetentuan).where(SyaratKetentuan.cabang_id == cu.cabang_id))
    sk = r_sk.scalar_one_or_none()

    data = {
        "tipe_nota": "FAKTUR / NOTA PENJUALAN",
        "no_nota": o.no_invoice,
        "tanggal": o.created_at.strftime("%d/%m/%Y %H:%M"),
        "pelanggan_nama": o.pelanggan.nama if o.pelanggan else "Pelanggan Umum",
        "pelanggan_hp": o.pelanggan.no_hp if o.pelanggan else "-",
        "nama_barang": "Transaksi Penjualan Produk",
        "imei": "-",
        "kerusakan": f"Penjualan ({len(o.details)} item)",
        "catatan": o.catatan or "-",
        "total_biaya": float(o.total or 0),
        "dp": float(o.dp or 0),
        "sisa_bayar": float(o.sisa_bayar or 0),
        "status": o.status,
        "spareparts": [
            {
                "nama": d.nama_produk,
                "qty": d.qty,
                "harga": float(d.harga or 0),
                "subtotal": float(d.total or 0),
            }
            for d in o.details
        ],
        "petugas": o.user.nama if o.user else "Kasir",
    }

    store_info = {
        "nama_toko": setting.nama_toko if setting and setting.nama_toko else "SARABABISA PREMIUM",
        "alamat": setting.alamat if setting else "",
        "no_hp": setting.no_hp if setting else "",
        "syarat_ketentuan": sk.isi_penjualan if sk and sk.isi_penjualan else None,
    }

    pdf = generate_nota_inkjet(data, store_info)
    return StreamingResponse(
        pdf, media_type="application/pdf",
        headers={"Content-Disposition": f"inline; filename=nota_penjualan_{o.no_invoice}.pdf"}
    )


# ─── CETAK NOTA TUKAR TAMBAH ───────────────────────────────
@router.get("/produk/tukar-tambah/{id}/cetak/inkjet")
async def cetak_tukar_tambah_inkjet(
    id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_any)
):
    from app.utils.pdf_generator import generate_nota_inkjet

    r = await db.execute(select(TukarTambah).where(TukarTambah.id == id, TukarTambah.cabang_id == cu.cabang_id))
    tt = r.scalar_one_or_none()
    if not tt:
        raise HTTPException(404, "Data tukar tambah tidak ditemukan")

    r_store = await db.execute(select(StoreSetting).where(StoreSetting.cabang_id == cu.cabang_id))
    setting = r_store.scalar_one_or_none()

    r_pel = await db.execute(select(Pelanggan).where(Pelanggan.id == tt.pelanggan_id))
    pel = r_pel.scalar_one_or_none()

    r_items = await db.execute(select(TukarTambahDetail).where(TukarTambahDetail.tukar_tambah_id == id))
    details = r_items.scalars().all()

    data = {
        "tipe_nota": "NOTA TUKAR TAMBAH",
        "no_nota": tt.no_tt or tt.no_nota or f"TT-{tt.id}",
        "tanggal": tt.created_at.strftime("%d/%m/%Y %H:%M"),
        "pelanggan_nama": pel.nama if pel else "-",
        "pelanggan_hp": pel.no_hp if pel else "-",
        "nama_barang": f"HP Lama: {tt.nama_hp_lama or '-'} ({tt.kondisi or '-'})",
        "imei": "-",
        "kerusakan": f"Tukar Tambah",
        "catatan": f"Nilai Beli: Rp {float(tt.total_beli or 0):,.0f} | Nilai Tukar: Rp {float(tt.harga_tukar or 0):,.0f} | Selisih: Rp {float(tt.selisih or 0):,.0f}",
        "total_biaya": float(tt.total_beli or 0),
        "dp": float(tt.harga_tukar or 0),
        "sisa_bayar": float(tt.selisih or 0),
        "status": tt.status,
        "spareparts": [
            {
                "nama": d.nama_produk or "Produk",
                "qty": d.qty,
                "harga": float(d.harga or 0),
                "subtotal": float(d.harga or 0) * d.qty,
            }
            for d in details
        ],
        "petugas": "Admin",
    }

    store_info = {
        "nama_toko": setting.nama_toko if setting and setting.nama_toko else "SARABABISA PREMIUM",
        "alamat": setting.alamat if setting else "",
        "no_hp": setting.no_hp if setting else "",
    }

    pdf = generate_nota_inkjet(data, store_info)
    return StreamingResponse(
        pdf, media_type="application/pdf",
        headers={"Content-Disposition": f"inline; filename=nota_tt_{data['no_nota']}.pdf"}
    )

