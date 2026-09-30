"""
POS (Point of Sale) API
Cart disimpan di Redis per user — bukan di client session.
Multi-cabang: cart key menggunakan user_id, produk difilter per cabang.
"""
from fastapi import APIRouter, Depends, HTTPException, Query
from fastapi.responses import StreamingResponse
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, and_, update, func
from typing import Optional
import json, uuid
from datetime import datetime

from app.core.database import get_db
from app.core.security import require_any, require_admin_toko
from app.core.cache import get_redis
from app.models.models import Produk, Order, OrderDetail, User, StoreSetting, MetodePembayaran
from app.utils.query import paginate, soft_delete_filter
from app.utils.notifications import notif_penjualan_baru

router = APIRouter()

CART_TTL = 3600 * 6  # 6 jam


# ─── Cart Helpers ─────────────────────────────────────────
async def _get_cart(user_id: int) -> dict:
    redis = await get_redis()
    key = f"pos:cart:{user_id}"
    data = await redis.get(key)
    return json.loads(data) if data else {"items": [], "diskon": 0, "diskon_type": "nominal"}


async def _save_cart(user_id: int, cart: dict):
    redis = await get_redis()
    key = f"pos:cart:{user_id}"
    await redis.setex(key, CART_TTL, json.dumps(cart))


async def _clear_cart(user_id: int):
    redis = await get_redis()
    await redis.delete(f"pos:cart:{user_id}")


# ─── POS Endpoints ─────────────────────────────────────────
@router.get("/pos/produk")
async def pos_all_produk(
    search: Optional[str] = Query(None),
    tipe: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_any),
):
    """Ambil semua produk yang tersedia di cabang (stok > 0)."""
    base = and_(
        Produk.cabang_id == cu.cabang_id,
        Produk.stok > 0,
        Produk.deleted_at.is_(None),
    )
    q = select(
        Produk.id, Produk.nama, Produk.kode, Produk.barcode,
        Produk.harga_jual, Produk.stok, Produk.tipe, Produk.foto, Produk.satuan,
    ).where(base).order_by(Produk.nama)

    if tipe:
        q = q.where(Produk.tipe == tipe)
    if search:
        t = f"%{search}%"
        q = q.where(Produk.nama.ilike(t) | Produk.kode.ilike(t) | Produk.barcode.ilike(t))

    r = await db.execute(q.limit(200))
    return r.mappings().all()


@router.get("/pos/cart")
async def get_cart(cu=Depends(require_any)):
    """Ambil cart aktif user dari Redis."""
    cart = await _get_cart(cu.id)
    total = sum(item["subtotal"] for item in cart["items"])
    diskon_amount = 0.0
    if cart["diskon"] > 0:
        if cart["diskon_type"] == "persen":
            diskon_amount = total * cart["diskon"] / 100
        else:
            diskon_amount = float(cart["diskon"])
    grand_total = max(0, total - diskon_amount)
    return {
        "items": cart["items"],
        "diskon": cart["diskon"],
        "diskon_type": cart["diskon_type"],
        "subtotal": total,
        "diskon_amount": diskon_amount,
        "grand_total": grand_total,
    }


@router.post("/pos/cart/add")
async def add_to_cart(
    body: dict,
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_any),
):
    """Tambah produk ke cart."""
    produk_id = body.get("produk_id")
    qty = int(body.get("qty", 1))
    harga_custom = body.get("harga_jual")  # bisa override

    r = await db.execute(
        select(Produk).where(
            Produk.id == produk_id,
            Produk.cabang_id == cu.cabang_id,
            Produk.deleted_at.is_(None),
        )
    )
    produk = r.scalar_one_or_none()
    if not produk:
        raise HTTPException(404, "Produk tidak ditemukan")
    if produk.stok < qty:
        raise HTTPException(400, f"Stok tidak cukup. Stok tersedia: {produk.stok}")

    harga = float(harga_custom or produk.harga_jual or 0)
    cart = await _get_cart(cu.id)

    # Cek sudah ada di cart?
    for item in cart["items"]:
        if item["produk_id"] == produk_id:
            item["qty"] += qty
            item["subtotal"] = item["harga"] * item["qty"]
            await _save_cart(cu.id, cart)
            return {"message": "Qty diupdate", "cart": cart}

    row_id = str(uuid.uuid4())[:8]
    cart["items"].append({
        "row_id": row_id,
        "produk_id": produk_id,
        "nama": produk.nama,
        "kode": produk.kode,
        "harga": harga,
        "harga_modal": float(produk.harga_beli or 0),
        "qty": qty,
        "subtotal": harga * qty,
        "satuan": produk.satuan,
        "imei": produk.imei,
    })
    await _save_cart(cu.id, cart)
    return {"message": "Produk ditambahkan", "row_id": row_id}


@router.post("/pos/cart/update/{row_id}")
async def update_cart_item(row_id: str, body: dict, cu=Depends(require_any)):
    cart = await _get_cart(cu.id)
    qty = int(body.get("qty", 1))
    for item in cart["items"]:
        if item["row_id"] == row_id:
            item["qty"] = qty
            item["subtotal"] = item["harga"] * qty
    await _save_cart(cu.id, cart)
    return {"message": "Cart diupdate"}


@router.delete("/pos/cart/remove/{row_id}")
async def remove_cart_item(row_id: str, cu=Depends(require_any)):
    cart = await _get_cart(cu.id)
    cart["items"] = [i for i in cart["items"] if i["row_id"] != row_id]
    await _save_cart(cu.id, cart)
    return {"message": "Item dihapus dari cart"}


@router.post("/pos/cart/discount")
async def apply_discount(body: dict, cu=Depends(require_any)):
    cart = await _get_cart(cu.id)
    cart["diskon"] = float(body.get("diskon", 0))
    cart["diskon_type"] = body.get("diskon_type", "nominal")  # nominal | persen
    await _save_cart(cu.id, cart)
    return {"message": "Diskon diterapkan"}


@router.post("/pos/complete-order", status_code=201)
async def complete_order(
    body: dict,
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_any),
):
    """
    Selesaikan transaksi POS:
    1. Validasi stok
    2. Buat Order + OrderDetail
    3. Kurangi stok produk
    4. Clear cart
    5. Kirim notif Telegram
    """
    cart = await _get_cart(cu.id)
    if not cart["items"]:
        raise HTTPException(400, "Cart kosong")

    pelanggan_id = body.get("pelanggan_id")
    metode_id = body.get("metode_pembayaran_id")
    dp = float(body.get("dp", 0))
    catatan = body.get("catatan", "")

    # Hitung total
    subtotal = sum(i["subtotal"] for i in cart["items"])
    diskon = cart["diskon"]
    diskon_type = cart["diskon_type"]
    diskon_amount = subtotal * diskon / 100 if diskon_type == "persen" else float(diskon)
    total = max(0, subtotal - diskon_amount)
    sisa = total - dp

    # Cek setting pajak
    r = await db.execute(select(StoreSetting).where(StoreSetting.cabang_id == cu.cabang_id))
    setting = r.scalar_one_or_none()
    ppn = 0
    if setting and setting.is_tax:
        ppn = total * (setting.ppn or 11) / 100
        total += ppn

    # Nomor invoice
    from app.utils.nomor import generate_no_nota
    no_invoice = await generate_no_nota(db, cu.cabang_id, "INV")

    # Buat order
    order = Order(
        no_invoice=no_invoice,
        cabang_id=cu.cabang_id,
        user_id=cu.id,
        pelanggan_id=pelanggan_id,
        metode_pembayaran_id=metode_id,
        subtotal=subtotal,
        diskon=diskon_amount,
        ppn=ppn,
        total=total,
        dp=dp,
        sisa_bayar=sisa,
        catatan=catatan,
        status="lunas" if sisa <= 0 else "belum_lunas",
        is_approve="Setuju",
        tgl_disetujui=datetime.utcnow(),
    )
    db.add(order)
    await db.flush()

    # Order details + kurangi stok
    for item in cart["items"]:
        r2 = await db.execute(
            select(Produk).where(Produk.id == item["produk_id"], Produk.cabang_id == cu.cabang_id)
        )
        produk = r2.scalar_one_or_none()
        if not produk or produk.stok < item["qty"]:
            raise HTTPException(400, f"Stok produk '{item['nama']}' tidak mencukupi.")

        # Hitung profit (jika setting is_profit_produk aktif)
        profit = 0
        if setting and setting.is_profit_produk and setting.is_modal_produk:
            profit = (item["harga"] - float(produk.harga_beli or 0)) * item["qty"]

        db.add(OrderDetail(
            order_id=order.id,
            produk_id=item["produk_id"],
            nama_produk=item["nama"],
            qty=item["qty"],
            harga=item["harga"],
            harga_modal=item["harga_modal"],
            diskon=0,
            total=item["subtotal"],
            profit=profit,
            user_id=cu.id,
        ))

        # Kurangi stok
        await db.execute(
            update(Produk)
            .where(Produk.id == item["produk_id"])
            .values(stok=Produk.stok - item["qty"])
        )

    # Clear cart
    await _clear_cart(cu.id)

    # Notif Telegram
    try:
        await notif_penjualan_baru(db, cu.cabang_id, {
            "no_invoice": no_invoice,
            "pelanggan_nama": body.get("pelanggan_nama", "-"),
            "total": total,
            "metode_bayar": body.get("metode_nama", "-"),
            "kasir": cu.nama,
        })
    except Exception:
        pass

    return {
        "message": "Transaksi berhasil",
        "order_id": order.id,
        "no_invoice": no_invoice,
        "total": total,
    }


@router.get("/pos/{order_id}")
async def get_order_detail(
    order_id: int,
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_any),
):
    """Detail order untuk cetak nota setelah transaksi."""
    from sqlalchemy.orm import selectinload
    r = await db.execute(
        select(Order)
        .options(selectinload(Order.details), selectinload(Order.pelanggan))
        .where(Order.id == order_id, Order.cabang_id == cu.cabang_id)
    )
    order = r.scalar_one_or_none()
    if not order:
        raise HTTPException(404, "Order tidak ditemukan")

    return {
        "id": order.id,
        "no_invoice": order.no_invoice,
        "pelanggan": {"nama": order.pelanggan.nama} if order.pelanggan else None,
        "total": float(order.total or 0),
        "diskon": float(order.diskon or 0),
        "ppn": float(order.ppn or 0),
        "dp": float(order.dp or 0),
        "sisa_bayar": float(order.sisa_bayar or 0),
        "status": order.status,
        "catatan": order.catatan,
        "created_at": order.created_at,
        "items": [
            {
                "nama_produk": d.nama_produk,
                "qty": d.qty,
                "harga": float(d.harga or 0),
                "total": float(d.total or 0),
            }
            for d in order.details
        ],
    }


@router.get("/pos/{order_id}/cetak/termal")
async def cetak_pos_termal(
    order_id: int,
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_any),
):
    from sqlalchemy.orm import selectinload
    from app.utils.pdf_generator import generate_nota_penjualan_termal

    r = await db.execute(
        select(Order)
        .options(selectinload(Order.details), selectinload(Order.pelanggan), selectinload(Order.user))
        .where(Order.id == order_id, Order.cabang_id == cu.cabang_id)
    )
    order = r.scalar_one_or_none()
    if not order:
        raise HTTPException(404, "Order tidak ditemukan")

    r_store = await db.execute(select(StoreSetting).where(StoreSetting.cabang_id == cu.cabang_id))
    setting = r_store.scalar_one_or_none()

    order_data = {
        "invoice_no": order.no_invoice,
        "tanggal": order.created_at.strftime("%d/%m/%Y %H:%M"),
        "kasir": order.user.nama if order.user else cu.nama,
        "pelanggan_nama": order.pelanggan.nama if order.pelanggan else "Pelanggan Umum",
        "sub_total": float(order.total or 0) + float(order.diskon or 0),
        "diskon": float(order.diskon or 0),
        "total": float(order.total or 0),
        "bayar": float(order.bayar or order.total or 0),
        "kembali": float(order.kembali or 0),
        "metode_pembayaran": getattr(order, "payment_method", "Tunai") or "Tunai",
        "items": [
            {
                "nama_produk": d.nama_produk,
                "qty": d.qty,
                "harga": float(d.harga or 0),
                "total": float(d.total or 0),
            }
            for d in order.details
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
        headers={"Content-Disposition": f"inline; filename=nota_pos_{order.no_invoice}.pdf"}
    )


@router.get("/pos/{order_id}/cetak/inkjet")
async def cetak_pos_inkjet(
    order_id: int,
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_any),
):
    from sqlalchemy.orm import selectinload
    from app.utils.pdf_generator import generate_nota_inkjet
    from app.models.models import SyaratKetentuan

    r = await db.execute(
        select(Order)
        .options(selectinload(Order.details), selectinload(Order.pelanggan), selectinload(Order.user))
        .where(Order.id == order_id, Order.cabang_id == cu.cabang_id)
    )
    order = r.scalar_one_or_none()
    if not order:
        raise HTTPException(404, "Order tidak ditemukan")

    r_store = await db.execute(select(StoreSetting).where(StoreSetting.cabang_id == cu.cabang_id))
    setting = r_store.scalar_one_or_none()

    r_sk = await db.execute(select(SyaratKetentuan).where(SyaratKetentuan.cabang_id == cu.cabang_id))
    sk = r_sk.scalar_one_or_none()

    data = {
        "tipe_nota": "STRUK / NOTA PENJUALAN POS",
        "no_nota": order.no_invoice,
        "tanggal": order.created_at.strftime("%d/%m/%Y %H:%M"),
        "pelanggan_nama": order.pelanggan.nama if order.pelanggan else "Pelanggan Umum",
        "pelanggan_hp": order.pelanggan.no_hp if order.pelanggan else "-",
        "nama_barang": "Penjualan Barang / Produk",
        "imei": "-",
        "kerusakan": f"Transaksi Penjualan ({len(order.details)} item)",
        "catatan": order.catatan or "-",
        "total_biaya": float(order.total or 0),
        "dp": float(order.dp or 0),
        "sisa_bayar": float(order.sisa_bayar or 0),
        "status": order.status,
        "spareparts": [
            {
                "nama": d.nama_produk,
                "qty": d.qty,
                "harga": float(d.harga or 0),
                "subtotal": float(d.total or 0),
            }
            for d in order.details
        ],
        "petugas": order.user.nama if order.user else cu.nama,
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
        headers={"Content-Disposition": f"inline; filename=nota_pos_{order.no_invoice}.pdf"}
    )

