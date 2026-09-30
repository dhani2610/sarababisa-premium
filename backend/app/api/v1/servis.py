"""
Servis API — TransaksiServis dengan full CRUD dan workflow status
Optimized: selectinload untuk joins, indexed filter columns
Multi-cabang: semua query difilter per cabang_id.
Notifikasi: Telegram (per cabang token) + Fonnte WA saat status berubah.
Setting-aware: is_edit_transaksi, approval_hapus_transaksi dari StoreSetting.
"""
from fastapi import APIRouter, Depends, Query, HTTPException, Path, BackgroundTasks, UploadFile, File
from fastapi.responses import StreamingResponse
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func, and_, or_, update, delete
from sqlalchemy.orm import selectinload
from typing import Optional, List
from datetime import datetime, date
import os, aiofiles

from app.core.database import get_db
from app.core.security import (
    get_current_user, require_any, require_admin_toko, require_kepala_toko,
    check_edit_transaksi_allowed, check_approval_hapus_required,
)
from app.core.cache import cache_delete_pattern, CacheTTL
from app.models.models import (
    TransaksiServis, Pelanggan, User, JenisBarang, Merek, ModelSeri,
    TindakanServis, TransaksiServisSparepart, HistoryGaransi,
    StoreSetting, PersetujuanHapusTransaksi, TransaksiServisTeknisi,
    SyaratKetentuan, AuditLog,
)
from app.utils.pdf_generator import (
    generate_nota_termal, generate_nota_inkjet, generate_nota_qc
)
from app.utils.query import paginate, soft_delete_filter
from app.utils.nomor import generate_no_nota
from app.utils.notifications import (
    notif_servis_baru, notif_servis_bisa_diambil, notif_servis_sudah_diambil
)

router = APIRouter()


def _servis_columns():
    """Kolom yang diselect untuk list view — bukan SELECT *"""
    return [
        TransaksiServis.id,
        TransaksiServis.no_nota,
        TransaksiServis.status,
        TransaksiServis.kerusakan,
        TransaksiServis.total_biaya,
        TransaksiServis.dp,
        TransaksiServis.sisa_bayar,
        TransaksiServis.tgl_masuk,
        TransaksiServis.tgl_selesai,
        TransaksiServis.garansi_hari,
        TransaksiServis.is_approved,
        TransaksiServis.created_at,
        TransaksiServis.pelanggan_id,
        TransaksiServis.teknisi_id,
        TransaksiServis.merek_id,
        TransaksiServis.model_seri_id,
    ]


@router.get("/servis")
@router.get("/servis/transaksi-servis")
async def list_servis(
    page: int = Query(1, ge=1),
    per_page: int = Query(15, ge=1, le=100),
    status: Optional[str] = Query(None),
    search: Optional[str] = Query(None),
    teknisi_id: Optional[int] = Query(None),
    date_from: Optional[date] = Query(None),
    date_to: Optional[date] = Query(None),
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    cabang_id = current_user.cabang_id

    # Base query — JOIN dengan pelanggan, merek, model_seri (1 query)
    data_q = (
        select(
            TransaksiServis.id,
            TransaksiServis.no_nota,
            TransaksiServis.status,
            TransaksiServis.kerusakan,
            TransaksiServis.total_biaya,
            TransaksiServis.dp,
            TransaksiServis.sisa_bayar,
            TransaksiServis.tgl_masuk,
            TransaksiServis.tgl_selesai,
            TransaksiServis.garansi_hari,
            TransaksiServis.created_at,
            Pelanggan.nama.label("pelanggan_nama"),
            Pelanggan.no_hp.label("pelanggan_no_hp"),
            Merek.nama.label("merek_nama"),
            ModelSeri.nama.label("model_seri_nama"),
            User.nama.label("teknisi_nama"),
        )
        .outerjoin(Pelanggan, Pelanggan.id == TransaksiServis.pelanggan_id)
        .outerjoin(Merek, Merek.id == TransaksiServis.merek_id)
        .outerjoin(ModelSeri, ModelSeri.id == TransaksiServis.model_seri_id)
        .outerjoin(User, User.id == TransaksiServis.teknisi_id)
        .where(
            TransaksiServis.cabang_id == cabang_id,
            TransaksiServis.deleted_at.is_(None),
        )
        .order_by(TransaksiServis.created_at.desc())
    )

    count_q = (
        select(func.count(TransaksiServis.id))
        .where(
            TransaksiServis.cabang_id == cabang_id,
            TransaksiServis.deleted_at.is_(None),
        )
    )

    # Filters
    if status:
        data_q = data_q.where(TransaksiServis.status == status)
        count_q = count_q.where(TransaksiServis.status == status)
    if teknisi_id:
        data_q = data_q.where(TransaksiServis.teknisi_id == teknisi_id)
        count_q = count_q.where(TransaksiServis.teknisi_id == teknisi_id)
    if date_from:
        data_q = data_q.where(TransaksiServis.tgl_masuk >= date_from)
        count_q = count_q.where(TransaksiServis.tgl_masuk >= date_from)
    if date_to:
        data_q = data_q.where(TransaksiServis.tgl_masuk <= date_to)
        count_q = count_q.where(TransaksiServis.tgl_masuk <= date_to)
    if search:
        term = f"%{search}%"
        cond = or_(
            TransaksiServis.no_nota.ilike(term),
            Pelanggan.nama.ilike(term),
            Pelanggan.no_hp.ilike(term),
            TransaksiServis.kerusakan.ilike(term),
        )
        data_q = data_q.where(cond)

    return await paginate(db, data_q, count_q, page, per_page)


@router.get("/servis/{id}")
@router.get("/servis/transaksi-servis/{id}")
async def get_servis(
    id: int,
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    result = await db.execute(
        select(TransaksiServis)
        .options(
            selectinload(TransaksiServis.pelanggan),
            selectinload(TransaksiServis.user),
            selectinload(TransaksiServis.teknisi),
            selectinload(TransaksiServis.jenis_barang),
            selectinload(TransaksiServis.merek),
            selectinload(TransaksiServis.model_seri),
            selectinload(TransaksiServis.tindakan),
            selectinload(TransaksiServis.sparepart_items),
        )
        .where(
            TransaksiServis.id == id,
            TransaksiServis.cabang_id == current_user.cabang_id,
            TransaksiServis.deleted_at.is_(None),
        )
    )
    servis = result.scalar_one_or_none()
    if not servis:
        raise HTTPException(404, "Transaksi tidak ditemukan")

    data = {
        "id": servis.id,
        "no_nota": servis.no_nota,
        "status": servis.status,
        "pelanggan": {
            "id": servis.pelanggan.id if servis.pelanggan else None,
            "nama": servis.pelanggan.nama if servis.pelanggan else None,
            "no_hp": servis.pelanggan.no_hp if servis.pelanggan else None,
        } if servis.pelanggan else None,
        "teknisi": {
            "id": servis.teknisi.id if servis.teknisi else None,
            "nama": servis.teknisi.nama if servis.teknisi else None,
        } if servis.teknisi else None,
        "merek": servis.merek.nama if servis.merek else None,
        "model_seri": servis.model_seri.nama if servis.model_seri else None,
        "tindakan": servis.tindakan.nama if servis.tindakan else None,
        "kerusakan": servis.kerusakan,
        "catatan_teknisi": servis.catatan_teknisi,
        "kondisi_barang": servis.kondisi_barang,
        "kelengkapan": servis.kelengkapan,
        "imei": servis.imei,
        "pin_pola": servis.pin_pola,
        "biaya_tindakan": float(servis.biaya_tindakan or 0),
        "biaya_sparepart": float(servis.biaya_sparepart or 0),
        "biaya_lain": float(servis.biaya_lain or 0),
        "diskon": float(servis.diskon or 0),
        "total_biaya": float(servis.total_biaya or 0),
        "dp": float(servis.dp or 0),
        "sisa_bayar": float(servis.sisa_bayar or 0),
        "garansi_hari": servis.garansi_hari,
        "tgl_masuk": servis.tgl_masuk,
        "tgl_selesai": servis.tgl_selesai,
        "tgl_ambil": servis.tgl_ambil,
        "spareparts": [
            {
                "id": sp.id,
                "nama_sparepart": sp.nama_sparepart,
                "qty": sp.qty,
                "harga": float(sp.harga or 0),
                "subtotal": float(sp.subtotal or 0),
            }
            for sp in servis.sparepart_items
        ],
        "created_at": servis.created_at,
    }

    # Load multi teknisi jika ada
    r_tek = await db.execute(
        select(TransaksiServisTeknisi, User.nama)
        .outerjoin(User, User.id == TransaksiServisTeknisi.teknisi_id)
        .where(TransaksiServisTeknisi.transaksi_servis_id == id)
    )
    data["multi_teknisi"] = [
        {
            "id": tst.id,
            "teknisi_id": tst.teknisi_id,
            "nama": nama or f"Teknisi #{tst.teknisi_id}",
            "is_utama": tst.is_utama,
            "tipe": tst.tipe,
            "biaya": float(tst.biaya or 0),
            "persen_teknisi": tst.persen_teknisi,
            "catatan": tst.catatan,
        }
        for tst, nama in r_tek.all()
    ]
    return data


@router.post("/servis/transaksi-servis", status_code=201)
async def create_servis(
    body: dict,
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    no_nota = await generate_no_nota(db, current_user.cabang_id, "SRV")

    servis = TransaksiServis(
        no_nota=no_nota,
        cabang_id=current_user.cabang_id,
        user_id=current_user.id,
        pelanggan_id=body.get("pelanggan_id"),
        teknisi_id=body.get("teknisi_id"),
        jenis_barang_id=body.get("jenis_barang_id"),
        merek_id=body.get("merek_id"),
        model_seri_id=body.get("model_seri_id"),
        tindakan_id=body.get("tindakan_id"),
        imei=body.get("imei"),
        kerusakan=body.get("kerusakan"),
        kondisi_barang=body.get("kondisi_barang"),
        kelengkapan=body.get("kelengkapan"),
        pin_pola=body.get("pin_pola"),
        biaya_tindakan=body.get("biaya_tindakan", 0),
        biaya_sparepart=body.get("biaya_sparepart", 0),
        biaya_lain=body.get("biaya_lain", 0),
        diskon=body.get("diskon", 0),
        total_biaya=body.get("total_biaya", 0),
        dp=body.get("dp", 0),
        sisa_bayar=body.get("sisa_bayar", 0),
        garansi_hari=body.get("garansi_hari", 30),
        tgl_masuk=body.get("tgl_masuk"),
        status="proses",
        metode_pembayaran_id=body.get("metode_pembayaran_id"),
    )
    db.add(servis)
    await db.flush()

    # Tambah sparepart items
    spareparts = body.get("spareparts", [])
    for sp in spareparts:
        db.add(TransaksiServisSparepart(
            transaksi_servis_id=servis.id,
            produk_id=sp.get("produk_id"),
            nama_sparepart=sp.get("nama_sparepart"),
            qty=sp.get("qty", 1),
            harga=sp.get("harga", 0),
            subtotal=sp.get("subtotal", 0),
        ))

    # Tambah multi-teknisi jika ada
    teknisis = body.get("multi_teknisi", []) or body.get("teknisi_list", [])
    for idx, tk in enumerate(teknisis):
        tek_id = tk.get("teknisi_id") or tk.get("user_id")
        if tek_id:
            db.add(TransaksiServisTeknisi(
                transaksi_servis_id=servis.id,
                teknisi_id=tek_id,
                is_utama=bool(tk.get("is_utama", idx == 0)),
                tipe=tk.get("tipe", "Hardware"),
                biaya=tk.get("biaya", 0),
                persen_teknisi=tk.get("persen_teknisi", 0),
                catatan=tk.get("catatan"),
            ))


    # Invalidate cache
    await cache_delete_pattern(f"servis:{current_user.cabang_id}:*")
    await cache_delete_pattern(f"dashboard:{current_user.cabang_id}:*")

    # Kirim notifikasi Telegram ke bot cabang
    try:
        r_pel = await db.execute(select(Pelanggan).where(Pelanggan.id == servis.pelanggan_id))
        pel = r_pel.scalar_one_or_none()
        await notif_servis_baru(db, current_user.cabang_id, {
            "status": servis.status,
            "no_nota": no_nota,
            "pelanggan_nama": pel.nama if pel else "-",
            "nama_barang": body.get("nama_barang", "-"),
            "kerusakan": servis.kerusakan,
            "estimasi_biaya": float(servis.total_biaya or 0),
            "dp": float(servis.dp or 0),
            "tgl_masuk": str(servis.tgl_masuk or date.today()),
            "penerima": current_user.nama,
        })
    except Exception:
        pass  # Jangan gagalkan transaksi karena notif error

    return {"id": servis.id, "no_nota": no_nota, "message": "Transaksi berhasil dibuat"}


@router.patch("/servis/transaksi-servis/{id}/ubah-status")
async def ubah_status_servis(
    id: int,
    body: dict,
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    status_baru = body.get("status")
    valid_status = ["proses", "bisa_diambil", "sudah_diambil", "belum_disetujui", "batal"]
    if status_baru not in valid_status:
        raise HTTPException(400, f"Status tidak valid. Pilih dari: {valid_status}")

    values = {"status": status_baru, "updated_at": datetime.utcnow()}
    if status_baru == "bisa_diambil":
        values["tgl_selesai"] = date.today()
    elif status_baru == "sudah_diambil":
        values["tgl_ambil"] = date.today()
    if body.get("catatan_teknisi"):
        values["catatan_teknisi"] = body["catatan_teknisi"]

    await db.execute(
        update(TransaksiServis)
        .where(TransaksiServis.id == id, TransaksiServis.cabang_id == current_user.cabang_id)
        .values(**values)
    )
    await cache_delete_pattern(f"servis:{current_user.cabang_id}:*")
    await cache_delete_pattern(f"dashboard:{current_user.cabang_id}:*")

    # Kirim notifikasi berdasarkan status baru
    try:
        r_s = await db.execute(
            select(TransaksiServis, Pelanggan)
            .outerjoin(Pelanggan, Pelanggan.id == TransaksiServis.pelanggan_id)
            .where(TransaksiServis.id == id)
        )
        row = r_s.first()
        if row:
            srv, pel = row
            data = {
                "no_nota": srv.no_nota,
                "pelanggan_nama": pel.nama if pel else "-",
                "nama_barang": getattr(srv, "nama_barang", "-"),
                "total_biaya": float(srv.total_biaya or 0),
                "sisa_bayar": float(srv.sisa_bayar or 0),
                "tindakan": body.get("catatan_teknisi", "-"),
                "catatan_teknisi": body.get("catatan_teknisi"),
            }
            hp = pel.no_hp if pel else None
            if status_baru == "bisa_diambil":
                await notif_servis_bisa_diambil(db, current_user.cabang_id, data, hp)
            elif status_baru == "sudah_diambil":
                await notif_servis_sudah_diambil(db, current_user.cabang_id, data)
    except Exception:
        pass

    return {"message": f"Status diubah ke {status_baru}"}


@router.delete("/servis/transaksi-servis/{id}")
async def delete_servis(
    id: int,
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    """
    Hapus transaksi servis.
    Jika StoreSetting.approval_hapus_transaksi = True dan bukan KepalaToko,
    maka buat permintaan approval dulu (sama seperti Laravel TransactionApprovalHelper).
    """
    needs_approval = await check_approval_hapus_required(db, current_user.cabang_id)
    if needs_approval and not current_user.is_kepala_toko:
        r = await db.execute(select(TransaksiServis).where(TransaksiServis.id == id))
        srv = r.scalar_one_or_none()
        if not srv:
            raise HTTPException(404, "Transaksi tidak ditemukan")
        db.add(PersetujuanHapusTransaksi(
            tipe="servis", record_id=id, user_id=current_user.id,
            cabang_id=current_user.cabang_id, status="menunggu",
        ))
        return {"message": "Permintaan hapus diajukan ke Kepala Toko untuk persetujuan"}

    await db.execute(
        update(TransaksiServis)
        .where(TransaksiServis.id == id, TransaksiServis.cabang_id == current_user.cabang_id)
        .values(deleted_at=datetime.utcnow())
    )
    await cache_delete_pattern(f"servis:{current_user.cabang_id}:*")
    await cache_delete_pattern(f"dashboard:{current_user.cabang_id}:*")
    return {"message": "Transaksi dihapus"}


# ── Tindakan Servis ──
@router.get("/servis/tindakan-servis")
async def list_tindakan(
    page: int = Query(1, ge=1),
    per_page: int = Query(50, ge=1, le=200),
    search: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):                                                                    
    base = and_(
        TindakanServis.cabang_id == current_user.cabang_id,
        TindakanServis.deleted_at.is_(None),
    )
    q = select(TindakanServis.id, TindakanServis.nama, TindakanServis.harga).where(base)
    cq = select(func.count(TindakanServis.id)).where(base)
    if search:
        t = f"%{search}%"
        q = q.where(TindakanServis.nama.ilike(t))
        cq = cq.where(TindakanServis.nama.ilike(t))
    return await paginate(db, q, cq, page, per_page)


# ─── HELPER: DATA UNTUK PDF NOTA SERVIS ─────────────────────
async def _get_servis_pdf_data(db: AsyncSession, id: int, cabang_id: int):
    q = (
        select(TransaksiServis, Pelanggan, Merek, ModelSeri, JenisBarang, User)
        .outerjoin(Pelanggan, Pelanggan.id == TransaksiServis.pelanggan_id)
        .outerjoin(Merek, Merek.id == TransaksiServis.merek_id)
        .outerjoin(ModelSeri, ModelSeri.id == TransaksiServis.model_seri_id)
        .outerjoin(JenisBarang, JenisBarang.id == TransaksiServis.jenis_barang_id)
        .outerjoin(User, User.id == TransaksiServis.teknisi_id)
        .where(TransaksiServis.id == id, TransaksiServis.cabang_id == cabang_id)
    )
    row = (await db.execute(q)).first()
    if not row:
        raise HTTPException(404, "Data transaksi servis tidak ditemukan")

    srv, pel, mrk, mdl, jns, tek = row

    # Spareparts
    r_sp = await db.execute(
        select(TransaksiServisSparepart).where(TransaksiServisSparepart.transaksi_servis_id == id)
    )
    spareparts_data = [
        {
            "nama": sp.nama_sparepart or "Sparepart",
            "qty": sp.qty,
            "harga": float(sp.harga or 0),
            "subtotal": float(sp.subtotal or 0),
        }
        for sp in r_sp.scalars().all()
    ]

    # Multi Teknisi
    r_tek = await db.execute(
        select(TransaksiServisTeknisi, User.nama)
        .outerjoin(User, User.id == TransaksiServisTeknisi.teknisi_id)
        .where(TransaksiServisTeknisi.transaksi_servis_id == id)
    )
    multi_teknisi_data = [
        {"nama": nama or f"Teknisi #{tst.teknisi_id}", "tipe": tst.tipe or "Teknisi"}
        for tst, nama in r_tek.all()
    ]

    # StoreSetting
    r_store = await db.execute(select(StoreSetting).where(StoreSetting.cabang_id == cabang_id))
    setting = r_store.scalar_one_or_none()

    # SyaratKetentuan
    r_sk = await db.execute(select(SyaratKetentuan).where(SyaratKetentuan.cabang_id == cabang_id))
    sk = r_sk.scalar_one_or_none()

    parts = [jns.nama if jns else "", mrk.nama if mrk else "", mdl.nama if mdl else ""]
    nama_barang = " ".join([p for p in parts if p]).strip() or "Perangkat Servis"

    data = {
        "no_nota": srv.no_nota,
        "tanggal": srv.tgl_masuk.strftime("%d/%m/%Y") if srv.tgl_masuk else srv.created_at.strftime("%d/%m/%Y %H:%M"),
        "pelanggan_nama": pel.nama if pel else "Pelanggan Umum",
        "pelanggan_hp": pel.no_hp if pel else "-",
        "nama_barang": nama_barang,
        "imei": srv.imei or "-",
        "kerusakan": srv.kerusakan or "-",
        "catatan": srv.kondisi_barang or srv.catatan_teknisi or "-",
        "estimasi_biaya": float(srv.total_biaya or 0),
        "total_biaya": float(srv.total_biaya or 0),
        "dp": float(srv.dp or 0),
        "sisa_bayar": float(srv.sisa_bayar or 0),
        "status": srv.status,
        "teknisi_nama": tek.nama if tek else None,
        "teknisis": multi_teknisi_data,
        "spareparts": spareparts_data,
        "petugas": "Admin",
    }

    store_info = {
        "nama_toko": setting.nama_toko if setting and setting.nama_toko else "SARABABISA PREMIUM",
        "alamat": setting.alamat if setting else "",
        "no_hp": setting.no_hp if setting else "",
        "syarat_ketentuan": sk.isi_terima if sk else None,
    }

    return data, store_info


# ─── UPDATE SERVIS ──────────────────────────────────────────
@router.put("/servis/{id}")
@router.put("/servis/transaksi-servis/{id}")
async def update_servis(
    id: int,
    body: dict,
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    r = await db.execute(
        select(TransaksiServis).where(
            TransaksiServis.id == id,
            TransaksiServis.cabang_id == current_user.cabang_id,
            TransaksiServis.deleted_at.is_(None),
        )
    )
    servis = r.scalar_one_or_none()
    if not servis:
        raise HTTPException(404, "Transaksi tidak ditemukan")

    allowed = await check_edit_transaksi_allowed(current_user, db, servis.created_at)
    if not allowed:
        raise HTTPException(403, "Edit transaksi hari lalu tidak diizinkan. Hubungi Kepala Toko.")

    updatable = [
        "pelanggan_id", "teknisi_id", "jenis_barang_id", "merek_id", "model_seri_id",
        "tindakan_id", "imei", "kerusakan", "kondisi_barang", "kelengkapan",
        "pin_pola", "biaya_tindakan", "biaya_sparepart", "biaya_lain", "diskon",
        "total_biaya", "dp", "sisa_bayar", "garansi_hari", "catatan_teknisi",
        "metode_pembayaran_id", "status",
    ]
    vals = {k: body[k] for k in updatable if k in body}
    vals["updated_at"] = datetime.utcnow()

    await db.execute(
        update(TransaksiServis)
        .where(TransaksiServis.id == id, TransaksiServis.cabang_id == current_user.cabang_id)
        .values(**vals)
    )

    # Update spareparts jika ada
    if "spareparts" in body:
        await db.execute(delete(TransaksiServisSparepart).where(TransaksiServisSparepart.transaksi_servis_id == id))
        for sp in body["spareparts"]:
            db.add(TransaksiServisSparepart(
                transaksi_servis_id=id,
                produk_id=sp.get("produk_id"),
                nama_sparepart=sp.get("nama_sparepart"),
                qty=sp.get("qty", 1),
                harga=sp.get("harga", 0),
                subtotal=sp.get("subtotal", 0),
            ))

    # Update multi-teknisi jika ada
    if "multi_teknisi" in body or "teknisi_list" in body:
        teknisis = body.get("multi_teknisi", []) or body.get("teknisi_list", [])
        await db.execute(delete(TransaksiServisTeknisi).where(TransaksiServisTeknisi.transaksi_servis_id == id))
        for idx, tk in enumerate(teknisis):
            tek_id = tk.get("teknisi_id") or tk.get("user_id")
            if tek_id:
                db.add(TransaksiServisTeknisi(
                    transaksi_servis_id=id,
                    teknisi_id=tek_id,
                    is_utama=bool(tk.get("is_utama", idx == 0)),
                    tipe=tk.get("tipe", "Hardware"),
                    biaya=tk.get("biaya", 0),
                    persen_teknisi=tk.get("persen_teknisi", 0),
                    catatan=tk.get("catatan"),
                ))

    await cache_delete_pattern(f"servis:{current_user.cabang_id}:*")
    return {"message": "Transaksi servis berhasil diperbarui"}


# ─── BATCH DELETE SERVIS ───────────────────────────────────
@router.delete("/servis/transaksi-servis/batch")
async def batch_delete_servis(
    body: dict,
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_kepala_toko),
):
    ids = body.get("ids", [])
    await db.execute(
        update(TransaksiServis)
        .where(TransaksiServis.id.in_(ids), TransaksiServis.cabang_id == current_user.cabang_id)
        .values(deleted_at=datetime.utcnow())
    )
    await cache_delete_pattern(f"servis:{current_user.cabang_id}:*")
    return {"message": f"{len(ids)} transaksi servis dihapus"}


# ─── SUB STATUS LISTINGS ───────────────────────────────────
@router.get("/servis/bisa-diambil")
async def list_bisa_diambil(
    page: int = Query(1, ge=1),
    per_page: int = Query(15, ge=1, le=100),
    search: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    return await list_servis(page=page, per_page=per_page, status="bisa_diambil", search=search, db=db, current_user=current_user)


@router.get("/servis/sudah-diambil")
async def list_sudah_diambil(
    page: int = Query(1, ge=1),
    per_page: int = Query(15, ge=1, le=100),
    search: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    return await list_servis(page=page, per_page=per_page, status="sudah_diambil", search=search, db=db, current_user=current_user)


@router.get("/servis/belum-lunas")
async def list_belum_lunas(
    page: int = Query(1, ge=1),
    per_page: int = Query(15, ge=1, le=100),
    search: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    cabang_id = current_user.cabang_id
    base = and_(
        TransaksiServis.cabang_id == cabang_id,
        TransaksiServis.deleted_at.is_(None),
        TransaksiServis.sisa_bayar > 0,
    )
    q = (
        select(
            TransaksiServis.id, TransaksiServis.no_nota, TransaksiServis.status,
            TransaksiServis.kerusakan, TransaksiServis.total_biaya, TransaksiServis.dp,
            TransaksiServis.sisa_bayar, TransaksiServis.tgl_masuk, TransaksiServis.created_at,
            Pelanggan.nama.label("pelanggan_nama"), Pelanggan.no_hp.label("pelanggan_no_hp"),
            Merek.nama.label("merek_nama"), ModelSeri.nama.label("model_seri_nama"),
            User.nama.label("teknisi_nama"),
        )
        .outerjoin(Pelanggan, Pelanggan.id == TransaksiServis.pelanggan_id)
        .outerjoin(Merek, Merek.id == TransaksiServis.merek_id)
        .outerjoin(ModelSeri, ModelSeri.id == TransaksiServis.model_seri_id)
        .outerjoin(User, User.id == TransaksiServis.teknisi_id)
        .where(base)
        .order_by(TransaksiServis.created_at.desc())
    )
    cq = select(func.count(TransaksiServis.id)).where(base)
    if search:
        t = f"%{search}%"
        cond = or_(TransaksiServis.no_nota.ilike(t), Pelanggan.nama.ilike(t), Pelanggan.no_hp.ilike(t))
        q = q.where(cond)
        cq = cq.where(cond)
    return await paginate(db, q, cq, page, per_page)


@router.get("/servis/belum-disetujui")
async def list_belum_disetujui(
    page: int = Query(1, ge=1),
    per_page: int = Query(15, ge=1, le=100),
    search: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    cabang_id = current_user.cabang_id
    base = and_(
        TransaksiServis.cabang_id == cabang_id,
        TransaksiServis.deleted_at.is_(None),
        or_(TransaksiServis.status == "belum_disetujui", TransaksiServis.is_approved.is_(False)),
    )
    q = (
        select(
            TransaksiServis.id, TransaksiServis.no_nota, TransaksiServis.status,
            TransaksiServis.kerusakan, TransaksiServis.total_biaya, TransaksiServis.dp,
            TransaksiServis.sisa_bayar, TransaksiServis.tgl_masuk, TransaksiServis.created_at,
            Pelanggan.nama.label("pelanggan_nama"), Pelanggan.no_hp.label("pelanggan_no_hp"),
            Merek.nama.label("merek_nama"), ModelSeri.nama.label("model_seri_nama"),
            User.nama.label("teknisi_nama"),
        )
        .outerjoin(Pelanggan, Pelanggan.id == TransaksiServis.pelanggan_id)
        .outerjoin(Merek, Merek.id == TransaksiServis.merek_id)
        .outerjoin(ModelSeri, ModelSeri.id == TransaksiServis.model_seri_id)
        .outerjoin(User, User.id == TransaksiServis.teknisi_id)
        .where(base)
        .order_by(TransaksiServis.created_at.desc())
    )
    cq = select(func.count(TransaksiServis.id)).where(base)
    if search:
        t = f"%{search}%"
        cond = or_(TransaksiServis.no_nota.ilike(t), Pelanggan.nama.ilike(t))
        q = q.where(cond)
        cq = cq.where(cond)
    return await paginate(db, q, cq, page, per_page)


# ─── SERVIS LANGSUNG ───────────────────────────────────────
@router.post("/servis/transaksi-servis-langsung", status_code=201)
async def create_servis_langsung(
    body: dict,
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    no_nota = await generate_no_nota(db, current_user.cabang_id, "SRVL")
    total_biaya = float(body.get("total_biaya", 0))

    servis = TransaksiServis(
        no_nota=no_nota,
        cabang_id=current_user.cabang_id,
        user_id=current_user.id,
        pelanggan_id=body.get("pelanggan_id"),
        teknisi_id=body.get("teknisi_id") or current_user.id,
        jenis_barang_id=body.get("jenis_barang_id"),
        merek_id=body.get("merek_id"),
        model_seri_id=body.get("model_seri_id"),
        tindakan_id=body.get("tindakan_id"),
        imei=body.get("imei"),
        kerusakan=body.get("kerusakan", "Servis Langsung"),
        biaya_tindakan=body.get("biaya_tindakan", 0),
        biaya_sparepart=body.get("biaya_sparepart", 0),
        total_biaya=total_biaya,
        dp=total_biaya,
        sisa_bayar=0,
        garansi_hari=body.get("garansi_hari", 30),
        tgl_masuk=date.today(),
        tgl_selesai=date.today(),
        tgl_ambil=date.today(),
        status="sudah_diambil",
        is_approved=True,
        metode_pembayaran_id=body.get("metode_pembayaran_id"),
    )
    db.add(servis)
    await db.flush()

    for sp in body.get("spareparts", []):
        db.add(TransaksiServisSparepart(
            transaksi_servis_id=servis.id,
            produk_id=sp.get("produk_id"),
            nama_sparepart=sp.get("nama_sparepart"),
            qty=sp.get("qty", 1),
            harga=sp.get("harga", 0),
            subtotal=sp.get("subtotal", 0),
        ))

    await cache_delete_pattern(f"servis:{current_user.cabang_id}:*")
    return {"id": servis.id, "no_nota": no_nota, "message": "Servis langsung berhasil dicatat"}


# ─── STATUS TRANSITIONS ────────────────────────────────────
@router.post("/servis/{id}/kembali-proses")
async def kembali_proses(
    id: int,
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    await db.execute(
        update(TransaksiServis)
        .where(TransaksiServis.id == id, TransaksiServis.cabang_id == current_user.cabang_id)
        .values(status="proses", tgl_selesai=None, updated_at=datetime.utcnow())
    )
    await cache_delete_pattern(f"servis:{current_user.cabang_id}:*")
    return {"message": "Status servis dikembalikan ke proses"}


@router.post("/servis/{id}/kembali-bisa-diambil")
async def kembali_bisa_diambil(
    id: int,
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    await db.execute(
        update(TransaksiServis)
        .where(TransaksiServis.id == id, TransaksiServis.cabang_id == current_user.cabang_id)
        .values(status="bisa_diambil", tgl_ambil=None, updated_at=datetime.utcnow())
    )
    await cache_delete_pattern(f"servis:{current_user.cabang_id}:*")
    return {"message": "Status servis dikembalikan ke bisa diambil"}


@router.patch("/servis/{id}/approve")
async def approve_servis(
    id: int,
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_admin_toko),
):
    await db.execute(
        update(TransaksiServis)
        .where(TransaksiServis.id == id, TransaksiServis.cabang_id == current_user.cabang_id)
        .values(is_approved=True, status="proses", updated_at=datetime.utcnow())
    )
    await cache_delete_pattern(f"servis:{current_user.cabang_id}:*")
    return {"message": "Servis disetujui"}


@router.patch("/servis/{id}/reject")
async def reject_servis(
    id: int,
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_admin_toko),
):
    await db.execute(
        update(TransaksiServis)
        .where(TransaksiServis.id == id, TransaksiServis.cabang_id == current_user.cabang_id)
        .values(status="batal", updated_at=datetime.utcnow())
    )
    await cache_delete_pattern(f"servis:{current_user.cabang_id}:*")
    return {"message": "Servis ditolak"}


@router.patch("/servis/batch/approve")
async def batch_approve_servis(
    body: dict,
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_admin_toko),
):
    ids = body.get("ids", [])
    await db.execute(
        update(TransaksiServis)
        .where(TransaksiServis.id.in_(ids), TransaksiServis.cabang_id == current_user.cabang_id)
        .values(is_approved=True, status="proses", updated_at=datetime.utcnow())
    )
    await cache_delete_pattern(f"servis:{current_user.cabang_id}:*")
    return {"message": f"{len(ids)} servis disetujui"}


@router.patch("/servis/batch/reject")
async def batch_reject_servis(
    body: dict,
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_admin_toko),
):
    ids = body.get("ids", [])
    await db.execute(
        update(TransaksiServis)
        .where(TransaksiServis.id.in_(ids), TransaksiServis.cabang_id == current_user.cabang_id)
        .values(status="batal", updated_at=datetime.utcnow())
    )
    await cache_delete_pattern(f"servis:{current_user.cabang_id}:*")
    return {"message": f"{len(ids)} servis ditolak"}


# ─── MULTI TEKNISI ENDPOINTS ───────────────────────────────
@router.get("/servis/{id}/multi-teknisi")
async def get_multi_teknisi(
    id: int,
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    r_assigned = await db.execute(
        select(TransaksiServisTeknisi, User.nama)
        .outerjoin(User, User.id == TransaksiServisTeknisi.teknisi_id)
        .where(TransaksiServisTeknisi.transaksi_servis_id == id)
    )
    assigned = [
        {
            "id": t.id,
            "teknisi_id": t.teknisi_id,
            "nama": nama,
            "is_utama": t.is_utama,
            "tipe": t.tipe,
            "biaya": float(t.biaya or 0),
            "profit": float(t.profit or 0),
            "profittoko": float(t.profittoko or 0),
            "persen_teknisi": t.persen_teknisi,
            "catatan": t.catatan,
        }
        for t, nama in r_assigned.all()
    ]

    r_avail = await db.execute(
        select(User.id, User.nama).where(
            User.cabang_id == current_user.cabang_id,
            User.role == "Teknisi",
            User.is_active.is_(True),
            User.deleted_at.is_(None),
        ).order_by(User.nama)
    )
    avail = [{"id": u.id, "nama": u.nama} for u in r_avail.all()]

    return {"assigned": assigned, "available_teknisi": avail}


@router.post("/servis/{id}/multi-teknisi")
async def save_multi_teknisi(
    id: int,
    body: dict,
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_admin_toko),
):
    teknisis = body.get("teknisi_list", []) or body.get("teknisis", [])
    await db.execute(delete(TransaksiServisTeknisi).where(TransaksiServisTeknisi.transaksi_servis_id == id))
    for idx, tk in enumerate(teknisis):
        tek_id = tk.get("teknisi_id") or tk.get("user_id")
        if tek_id:
            db.add(TransaksiServisTeknisi(
                transaksi_servis_id=id,
                teknisi_id=tek_id,
                is_utama=bool(tk.get("is_utama", idx == 0)),
                tipe=tk.get("tipe", "Hardware"),
                modal_sparepart=tk.get("modal_sparepart", 0),
                biaya=tk.get("biaya", 0),
                profit=tk.get("profit", 0),
                profittoko=tk.get("profittoko", 0),
                persen_teknisi=tk.get("persen_teknisi", 0),
                catatan=tk.get("catatan"),
            ))
    return {"message": "Multi teknisi berhasil diperbarui"}


# ─── PIN POLA & FOTO ───────────────────────────────────────
@router.get("/servis/{id}/pin-pola")
async def get_pin_pola(id: int, db: AsyncSession = Depends(get_db), current_user=Depends(require_any)):
    r = await db.execute(
        select(TransaksiServis.id, TransaksiServis.no_nota, TransaksiServis.pin_pola)
        .where(TransaksiServis.id == id, TransaksiServis.cabang_id == current_user.cabang_id)
    )
    row = r.first()
    if not row:
        raise HTTPException(404, "Servis tidak ditemukan")
    return {"id": row.id, "no_nota": row.no_nota, "pin_pola": row.pin_pola}


@router.post("/servis/{id}/pin-pola")
async def save_pin_pola(id: int, body: dict, db: AsyncSession = Depends(get_db), current_user=Depends(require_any)):
    await db.execute(
        update(TransaksiServis)
        .where(TransaksiServis.id == id, TransaksiServis.cabang_id == current_user.cabang_id)
        .values(pin_pola=body.get("pin_pola"), updated_at=datetime.utcnow())
    )
    return {"message": "PIN/Pola berhasil disimpan"}


@router.post("/servis/{id}/foto")
async def upload_foto_servis(
    id: int,
    file: UploadFile = File(...),
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    ext = os.path.splitext(file.filename)[1]
    filename = f"servis_{id}_{int(datetime.utcnow().timestamp())}{ext}"
    path = f"storage/servis/{filename}"
    async with aiofiles.open(path, "wb") as out_file:
        content = await file.read()
        await out_file.write(content)
    return {"foto_path": path, "message": "Foto berhasil diunggah"}


# ─── CETAK NOTA PDF ENDPOINTS ──────────────────────────────
@router.get("/servis/{id}/cetak/termal")
async def cetak_servis_terima_termal(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    data, store = await _get_servis_pdf_data(db, id, cu.cabang_id)
    data["tipe_nota"] = "TANDA TERIMA SERVIS"
    pdf = generate_nota_termal(data, store)
    return StreamingResponse(
        pdf, media_type="application/pdf",
        headers={"Content-Disposition": f"inline; filename=nota_terima_{data['no_nota']}.pdf"}
    )


@router.get("/servis/{id}/cetak/inkjet")
async def cetak_servis_terima_inkjet(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    data, store = await _get_servis_pdf_data(db, id, cu.cabang_id)
    data["tipe_nota"] = "NOTA TANDA TERIMA SERVIS"
    pdf = generate_nota_inkjet(data, store)
    return StreamingResponse(
        pdf, media_type="application/pdf",
        headers={"Content-Disposition": f"inline; filename=nota_terima_{data['no_nota']}.pdf"}
    )


@router.get("/servis/{id}/cetak/pengambilan-termal")
async def cetak_servis_ambil_termal(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    data, store = await _get_servis_pdf_data(db, id, cu.cabang_id)
    data["tipe_nota"] = "NOTA PENGAMBILAN SERVIS"
    pdf = generate_nota_termal(data, store)
    return StreamingResponse(
        pdf, media_type="application/pdf",
        headers={"Content-Disposition": f"inline; filename=nota_ambil_{data['no_nota']}.pdf"}
    )


@router.get("/servis/{id}/cetak/pengambilan-inkjet")
async def cetak_servis_ambil_inkjet(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    data, store = await _get_servis_pdf_data(db, id, cu.cabang_id)
    data["tipe_nota"] = "NOTA PENGAMBILAN SERVIS"
    pdf = generate_nota_inkjet(data, store)
    return StreamingResponse(
        pdf, media_type="application/pdf",
        headers={"Content-Disposition": f"inline; filename=nota_ambil_{data['no_nota']}.pdf"}
    )


@router.get("/servis/{id}/cetak/qc")
async def cetak_servis_qc(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    data, store = await _get_servis_pdf_data(db, id, cu.cabang_id)
    pdf = generate_nota_qc(data, store)
    return StreamingResponse(
        pdf, media_type="application/pdf",
        headers={"Content-Disposition": f"inline; filename=qc_{data['no_nota']}.pdf"}
    )


# ─── HISTORY GARANSI ───────────────────────────────────────
@router.get("/history-garansi")
async def list_history_garansi(
    page: int = Query(1, ge=1),
    per_page: int = Query(20, ge=1, le=100),
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_any),
):
    q = (
        select(
            HistoryGaransi.id, HistoryGaransi.no_nota, HistoryGaransi.keterangan,
            HistoryGaransi.tgl_klaim, HistoryGaransi.created_at,
            TransaksiServis.kerusakan,
            Pelanggan.nama.label("pelanggan_nama"),
            Pelanggan.no_hp.label("pelanggan_no_hp"),
            User.nama.label("teknisi_nama"),
        )
        .join(TransaksiServis, TransaksiServis.id == HistoryGaransi.transaksi_servis_id)
        .outerjoin(Pelanggan, Pelanggan.id == TransaksiServis.pelanggan_id)
        .outerjoin(User, User.id == HistoryGaransi.teknisi_id)
        .where(TransaksiServis.cabang_id == cu.cabang_id)
        .order_by(HistoryGaransi.created_at.desc())
    )
    cq = select(func.count(HistoryGaransi.id)).join(
        TransaksiServis, TransaksiServis.id == HistoryGaransi.transaksi_servis_id
    ).where(TransaksiServis.cabang_id == cu.cabang_id)
    return await paginate(db, q, cq, page, per_page)


@router.post("/history-garansi", status_code=201)
async def create_history_garansi(
    body: dict,
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_any),
):
    garansi = HistoryGaransi(
        transaksi_servis_id=body["transaksi_servis_id"],
        no_nota=body.get("no_nota"),
        keterangan=body.get("keterangan"),
        tgl_klaim=body.get("tgl_klaim", date.today()),
        teknisi_id=body.get("teknisi_id") or cu.id,
    )
    db.add(garansi)
    await db.flush()
    return {"id": garansi.id, "message": "Klaim garansi berhasil dicatat"}


@router.get("/history-garansi/{id}")
async def get_history_garansi(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    r = await db.execute(select(HistoryGaransi).where(HistoryGaransi.id == id))
    g = r.scalar_one_or_none()
    if not g:
        raise HTTPException(404, "Data garansi tidak ditemukan")
    return g


# ─── LOG SERVIS ────────────────────────────────────────────
@router.get("/servis/log-servis")
async def list_log_servis(
    page: int = Query(1, ge=1),
    per_page: int = Query(30, ge=1, le=100),
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_any),
):
    base = and_(AuditLog.cabang_id == cu.cabang_id, AuditLog.model == "TransaksiServis")
    q = (
        select(AuditLog, User.nama.label("user_nama"))
        .outerjoin(User, User.id == AuditLog.user_id)
        .where(base)
        .order_by(AuditLog.created_at.desc())
    )
    cq = select(func.count(AuditLog.id)).where(base)
    return await paginate(db, q, cq, page, per_page)


@router.delete("/servis/log-servis/{id}")
async def delete_log_servis(
    id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)
):
    await db.execute(delete(AuditLog).where(AuditLog.id == id, AuditLog.cabang_id == cu.cabang_id))
    return {"message": "Log berhasil dihapus"}


# ─── EXTRA TINDAKAN SERVIS CRUD ────────────────────────────
@router.post("/servis/tindakan-servis", status_code=201)
async def create_tindakan(
    body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_admin_toko)
):
    tindakan = TindakanServis(
        nama=body["nama"],
        harga=body.get("harga", 0),
        cabang_id=cu.cabang_id,
    )
    db.add(tindakan)
    await db.flush()
    return {"id": tindakan.id, "message": "Tindakan servis berhasil dibuat"}


@router.put("/servis/tindakan-servis/{id}")
async def update_tindakan(
    id: int, body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_admin_toko)
):
    await db.execute(
        update(TindakanServis)
        .where(TindakanServis.id == id, TindakanServis.cabang_id == cu.cabang_id)
        .values(**{k: v for k, v in body.items() if k in ["nama", "harga"]})
    )
    return {"message": "Tindakan servis diperbarui"}


@router.delete("/servis/tindakan-servis/{id}")
async def delete_tindakan(
    id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_admin_toko)
):
    await db.execute(
        update(TindakanServis)
        .where(TindakanServis.id == id, TindakanServis.cabang_id == cu.cabang_id)
        .values(deleted_at=datetime.utcnow())
    )
    return {"message": "Tindakan servis dihapus"}


@router.delete("/servis/tindakan-servis/batch")
async def batch_delete_tindakan(
    body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_admin_toko)
):
    ids = body.get("ids", [])
    await db.execute(
        update(TindakanServis)
        .where(TindakanServis.id.in_(ids), TindakanServis.cabang_id == cu.cabang_id)
        .values(deleted_at=datetime.utcnow())
    )
    return {"message": f"{len(ids)} tindakan servis dihapus"}


# ─── CETAK NOTA QC PDF ──────────────────────────────────────
@router.get("/servis/{id}/cetak/qc")
@router.get("/servis/transaksi-servis/{id}/cetak/qc")
async def cetak_qc_endpoint(
    id: int,
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    data, store = await _get_servis_pdf_data(db, id, current_user.cabang_id)
    pdf_buffer = generate_nota_qc(data, store)
    return StreamingResponse(
        pdf_buffer,
        media_type="application/pdf",
        headers={"Content-Disposition": f"inline; filename=QC_{data.get('no_nota', id)}.pdf"},
    )


# ─── RIWAYAT GARANSI ENDPOINTS ─────────────────────────────
@router.get("/servis/garansi")
async def list_garansi(
    page: int = Query(1, ge=1),
    per_page: int = Query(15, ge=1, le=100),
    search: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    base = HistoryGaransi.cabang_id == current_user.cabang_id
    q = (
        select(
            HistoryGaransi.id,
            HistoryGaransi.date.label("tgl_klaim"),
            HistoryGaransi.service_id,
            HistoryGaransi.catatan,
            HistoryGaransi.status,
            HistoryGaransi.total_biaya,
            HistoryGaransi.tindakan,
            HistoryGaransi.sparepart,
            TransaksiServis.no_nota.label("no_nota_servis"),
            Pelanggan.nama.label("pelanggan_nama"),
            Pelanggan.no_hp.label("pelanggan_no_hp"),
            User.nama.label("teknisi_nama"),
        )
        .outerjoin(TransaksiServis, TransaksiServis.id == HistoryGaransi.service_id)
        .outerjoin(Pelanggan, Pelanggan.id == HistoryGaransi.id_customer)
        .outerjoin(User, User.id == HistoryGaransi.teknisi_id)
        .where(base)
        .order_by(HistoryGaransi.id.desc())
    )
    cq = select(func.count(HistoryGaransi.id)).where(base)
    if search:
        t = f"%{search}%"
        cond = or_(
            TransaksiServis.no_nota.ilike(t),
            Pelanggan.nama.ilike(t),
            HistoryGaransi.catatan.ilike(t),
        )
        q = q.where(cond)
        cq = cq.where(cond)

    return await paginate(db, q, cq, page, per_page)


@router.post("/servis/garansi", status_code=201)
async def create_garansi(
    body: dict,
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_any),
):
    garansi = HistoryGaransi(
        service_id=body["service_id"],
        id_customer=body.get("customer_id") or body.get("pelanggan_id"),
        teknisi_id=body.get("teknisi_id"),
        penerima_id=current_user.id,
        date=body.get("date") or date.today(),
        catatan=body.get("catatan", ""),
        status=body.get("status", "Proses"),
        total_biaya=body.get("total_biaya", 0),
        cabang_id=current_user.cabang_id,
    )
    db.add(garansi)
    await db.flush()
    return {"id": garansi.id, "message": "History garansi berhasil ditambahkan"}


@router.delete("/servis/garansi/{id}")
async def delete_garansi(
    id: int,
    db: AsyncSession = Depends(get_db),
    current_user=Depends(require_admin_toko),
):
    await db.execute(delete(HistoryGaransi).where(HistoryGaransi.id == id, HistoryGaransi.cabang_id == current_user.cabang_id))
    return {"message": "Data garansi berhasil dihapus"}


