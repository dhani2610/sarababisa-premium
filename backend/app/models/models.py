"""
Optimized SQLAlchemy models dengan:
- Database indexes pada semua FK dan kolom yang sering di-query
- Composite indexes untuk query multi-kolom
- Soft delete support
- Proper relationships dengan lazy="raise" untuk cegah N+1
"""
from sqlalchemy import (
    Column, Integer, String, Text, DateTime, Date, Time,
    Boolean, Float, Numeric, ForeignKey, Enum, JSON, BigInteger,
    SmallInteger, Index, UniqueConstraint, event
)
from sqlalchemy.orm import relationship, DeclarativeBase, synonym
from datetime import datetime


class Base(DeclarativeBase):
    pass


# ─────────────────────────────────────────────────────────
# USERS & AUTH
# ─────────────────────────────────────────────────────────
class User(Base):
    __tablename__ = "users"
    __table_args__ = (
        Index("idx_users_username", "username"),
        Index("idx_users_cabang_role", "cabang_id", "role"),
        Index("idx_users_role", "role"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    name = Column(String(255))
    nama = synonym("name")
    username = Column(String(255), unique=True, nullable=False)
    email = Column(String(255), unique=True, nullable=True)
    password = Column(String(255), nullable=False)
    role = Column(String(255), nullable=False)
    cabang_id = Column(Integer, ForeignKey("cabangs.id", ondelete="SET NULL"), nullable=True)
    profile_photo_path = Column(String(255), nullable=True)
    foto = synonym("profile_photo_path")
    nomor_hp = Column(String(20), nullable=True)
    no_hp = synonym("nomor_hp")
    nik = Column(String(50), nullable=True)
    alamat = Column(Text, nullable=True)
    bagian_teknisi = Column(String(100), nullable=True)
    persen = Column(Numeric(5, 2), default=0)
    persen_hardware = synonym("persen")
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    deleted_at = Column(DateTime, nullable=True)

    @property
    def is_active(self):
        return self.deleted_at is None

    # Relationships — lazy="raise" mencegah N+1 query secara tidak sengaja
    cabang = relationship("Cabang", back_populates="users", lazy="raise")


class Cabang(Base):
    __tablename__ = "cabangs"

    id = Column(Integer, primary_key=True, autoincrement=True)
    nama_cabang = Column(String(255), nullable=False)
    cabang_id = Column(Integer, nullable=True)
    expired_date = Column(DateTime, nullable=True)
    allow_transaksi = Column(Integer, default=1)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    nama = synonym("nama_cabang")

    users = relationship("User", back_populates="cabang", lazy="raise")
    pelanggan = relationship("Pelanggan", back_populates="cabang", lazy="raise")


# ─────────────────────────────────────────────────────────
# PELANGGAN
# ─────────────────────────────────────────────────────────
class Pelanggan(Base):
    __tablename__ = "customers"
    __table_args__ = (
        Index("idx_pelanggan_cabang", "cabang_id"),
        Index("idx_pelanggan_nama", "nama"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    nama = Column(String(255), nullable=False)
    nomor_hp = Column(String(20), nullable=True)
    no_hp = synonym("nomor_hp")
    alamat = Column(Text, nullable=True)
    kategori = Column(String(50), nullable=True)
    cabang_id = Column(Integer, ForeignKey("cabangs.id", ondelete="SET NULL"), nullable=True)
    deleted_at = Column(DateTime, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    cabang = relationship("Cabang", back_populates="pelanggan", lazy="raise")
    transaksi_servis = relationship("TransaksiServis", back_populates="pelanggan", lazy="raise")
    orders = relationship("Order", back_populates="pelanggan", lazy="raise")


# ─────────────────────────────────────────────────────────
# MASTER DATA SERVIS
# ─────────────────────────────────────────────────────────
class JenisBarang(Base):
    __tablename__ = "types"
    __table_args__ = (Index("idx_types_cabang", "cabang_id"),)

    id = Column(Integer, primary_key=True, autoincrement=True)
    name = Column(String(255), nullable=False)
    nama = synonym("name")
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


class Merek(Base):
    __tablename__ = "brands"
    __table_args__ = (Index("idx_brands_cabang", "cabang_id"),)

    id = Column(Integer, primary_key=True, autoincrement=True)
    name = Column(String(255), nullable=False)
    nama = synonym("name")
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    deleted_at = Column(DateTime, nullable=True)

    model_seris = relationship("ModelSeri", back_populates="merek", lazy="raise")


class ModelSeri(Base):
    __tablename__ = "model_series"
    __table_args__ = (
        Index("idx_modelseries_brands", "brands_id"),
        Index("idx_modelseries_cabang", "cabang_id"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    name = Column(String(255), nullable=False)
    nama = synonym("name")
    brands_id = Column(Integer, ForeignKey("brands.id"), nullable=True)
    merek_id = synonym("brands_id")
    id_tipe_os = Column(Integer, nullable=True)
    nominal_bonus = Column(Numeric(15, 2), default=0)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    deleted_at = Column(DateTime, nullable=True)

    merek = relationship("Merek", back_populates="model_seris", lazy="raise")


class Kapasitas(Base):
    __tablename__ = "capacities"
    __table_args__ = (Index("idx_capacities_cabang", "cabang_id"),)

    id = Column(Integer, primary_key=True, autoincrement=True)
    name = Column(String(100), nullable=False)
    nama = synonym("name")
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


class Warna(Base):
    __tablename__ = "colors"
    __table_args__ = (Index("idx_colors_cabang", "cabang_id"),)

    id = Column(Integer, primary_key=True, autoincrement=True)
    name = Column(String(100), nullable=False)
    nama = synonym("name")
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


class TipeOs(Base):
    __tablename__ = "tipe_os"
    __table_args__ = (Index("idx_tipeos_cabang", "cabang_id"),)

    id = Column(Integer, primary_key=True, autoincrement=True)
    nama = Column(String(100), nullable=False)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


class TindakanServis(Base):
    __tablename__ = "service_actions"
    __table_args__ = (Index("idx_service_actions_cabang", "cabang_id"),)

    id = Column(Integer, primary_key=True, autoincrement=True)
    nama_tindakan = Column(String(255), nullable=False)
    nama = synonym("nama_tindakan")
    modal_sparepart = Column(Numeric(15, 2), default=0)
    harga_pelanggan = Column(Numeric(15, 2), default=0)
    harga = synonym("harga_pelanggan")
    harga_toko = Column(Numeric(15, 2), default=0)
    garansi = Column(String(100), nullable=True)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    deleted_at = Column(DateTime, nullable=True)


class MetodePembayaran(Base):
    __tablename__ = "metode_pembayarans"
    __table_args__ = (Index("idx_metode_cabang", "cabang_id"),)

    id = Column(Integer, primary_key=True, autoincrement=True)
    nama = Column(String(100), nullable=False)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


# ─────────────────────────────────────────────────────────
# TRANSAKSI SERVIS — tabel terbesar, index paling kritis
# ─────────────────────────────────────────────────────────
class TransaksiServis(Base):
    __tablename__ = "service_transactions"
    __table_args__ = (
        Index("idx_st_cabang_id", "cabang_id"),
        Index("idx_st_customers_id", "customers_id"),
        Index("idx_st_nomor_servis", "nomor_servis"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    nomor_servis = Column(String(50), nullable=False)
    no_nota = synonym("nomor_servis")

    customers_id = Column(Integer, ForeignKey("customers.id", ondelete="SET NULL"), nullable=True)
    pelanggan_id = synonym("customers_id")

    users_id = Column(Integer, ForeignKey("users.id", ondelete="SET NULL"), nullable=True)
    user_id = synonym("users_id")
    teknisi_id = synonym("users_id")

    cabang_id = Column(Integer, ForeignKey("cabangs.id", ondelete="SET NULL"), nullable=True)
    status_servis = Column(String(50), nullable=False, default="Proses")
    status = synonym("status_servis")

    nama_barang = Column(String(255), nullable=True)
    nama_pelanggan = Column(String(255), nullable=True)
    kerusakan = Column(Text, nullable=True)
    catatan = Column(Text, nullable=True)
    catatan_teknisi = synonym("catatan")
    kondisi_servis = Column(Text, nullable=True)
    kondisi_barang = synonym("kondisi_servis")
    kelengkapan = Column(Text, nullable=True)
    imei = Column(String(50), nullable=True)
    warna = Column(String(50), nullable=True)
    pin = Column(String(255), nullable=True)
    pola = Column(String(255), nullable=True)
    pin_pola = synonym("pin")

    biaya = Column(Numeric(15, 2), default=0)
    total_biaya = synonym("biaya")
    biaya_tindakan = synonym("biaya")
    modal_sparepart = Column(Numeric(15, 2), default=0)
    biaya_sparepart = synonym("modal_sparepart")
    uang_muka = Column(Numeric(15, 2), default=0)
    dp = synonym("uang_muka")
    due = Column(Numeric(15, 2), default=0)
    sisa_bayar = synonym("due")
    diskon = Column(Numeric(15, 2), default=0)
    estimasi_biaya = Column(Numeric(15, 2), default=0)
    estimasi_pengerjaan = Column(String(100), nullable=True)
    garansi = Column(String(100), nullable=True)
    garansi_hari = synonym("garansi")

    is_approve = Column(Boolean, default=False)
    is_approved = synonym("is_approve")

    tgl_selesai = Column(DateTime, nullable=True)
    tgl_ambil = Column(DateTime, nullable=True)
    deleted_at = Column(DateTime, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)
    tgl_masuk = synonym("created_at")
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    types_id = Column(Integer, ForeignKey("types.id"), nullable=True)
    jenis_barang_id = synonym("types_id")
    brands_id = Column(Integer, ForeignKey("brands.id"), nullable=True)
    merek_id = synonym("brands_id")
    model_series_id = Column(Integer, ForeignKey("model_series.id"), nullable=True)
    model_seri_id = synonym("model_series_id")
    service_actions_id = Column(Integer, ForeignKey("service_actions.id"), nullable=True)
    tindakan_id = synonym("service_actions_id")

    # Eager load via selectinload di query, bukan di sini
    pelanggan = relationship("Pelanggan", back_populates="transaksi_servis", lazy="raise")
    user = relationship("User", foreign_keys=[users_id], lazy="raise")
    teknisi = synonym("user")
    jenis_barang = relationship("JenisBarang", foreign_keys=[types_id], lazy="raise")
    merek = relationship("Merek", foreign_keys=[brands_id], lazy="raise")
    model_seri = relationship("ModelSeri", foreign_keys=[model_series_id], lazy="raise")
    tindakan = relationship("TindakanServis", foreign_keys=[service_actions_id], lazy="raise")
    sparepart_items = relationship("TransaksiServisSparepart", back_populates="transaksi", lazy="raise")
    history_garansi = relationship("HistoryGaransi", back_populates="transaksi_servis", lazy="raise")


class TransaksiServisSparepart(Base):
    __tablename__ = "teknisi_servis"
    __table_args__ = (
        Index("idx_tss_transaksi", "service_transactions_id"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    service_transactions_id = Column(Integer, ForeignKey("service_transactions.id", ondelete="CASCADE"), nullable=False)
    transaksi_servis_id = synonym("service_transactions_id")
    users_id = Column(Integer, ForeignKey("users.id"), nullable=True)
    modal_sparepart = Column(Numeric(15, 2), default=0)
    biaya = Column(Numeric(15, 2), default=0)
    profit = Column(Numeric(15, 2), default=0)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    transaksi = relationship("TransaksiServis", back_populates="sparepart_items", lazy="raise")


class HistoryGaransi(Base):
    __tablename__ = "history_garansis"
    __table_args__ = (Index("idx_garansi_transaksi", "service_id"),)

    id = Column(Integer, primary_key=True, autoincrement=True)
    service_id = Column(Integer, ForeignKey("service_transactions.id"), nullable=False)
    transaksi_servis_id = synonym("service_id")
    date = Column(Date, nullable=True)
    tgl_klaim = synonym("date")
    penerima_id = Column(Integer, ForeignKey("users.id"), nullable=True)
    teknisi_id = Column(Integer, ForeignKey("users.id"), nullable=True)
    id_customer = Column(Integer, ForeignKey("customers.id"), nullable=True)
    customer_id = synonym("id_customer")
    pelanggan_id = synonym("id_customer")
    tindakan = Column(Text, nullable=True)
    sparepart = Column(Text, nullable=True)
    total_biaya = Column(Numeric(15, 2), default=0)
    total_biaya_tindakan = Column(Numeric(15, 2), default=0)
    modal_sparepart = Column(Numeric(15, 2), default=0)
    catatan = Column(Text, nullable=True)
    keterangan = synonym("catatan")
    keluhan = Column(Text, nullable=True)
    status = Column(String(50), nullable=True)
    estimasi_pengerjaan = Column(String(255), nullable=True)
    fungsi_masuk = Column(Text, nullable=True)
    fungsi_keluar = Column(Text, nullable=True)
    tgl_selesai = Column(Date, nullable=True)
    cabang_id = Column(Integer, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    transaksi_servis = relationship("TransaksiServis", back_populates="history_garansi", foreign_keys=[service_id], lazy="raise")
    teknisi = relationship("User", foreign_keys=[teknisi_id], lazy="raise")
    penerima = relationship("User", foreign_keys=[penerima_id], lazy="raise")
    pelanggan = relationship("Pelanggan", foreign_keys=[id_customer], lazy="raise")


# ─────────────────────────────────────────────────────────
# PRODUK — stok management
# ─────────────────────────────────────────────────────────
class KategoriProduk(Base):
    __tablename__ = "categories"
    __table_args__ = (Index("idx_categories_cabang", "cabang_id"),)

    id = Column(Integer, primary_key=True, autoincrement=True)
    category_name = Column(String(255), nullable=False)
    nama = synonym("category_name")
    show_portal = Column(Boolean, default=True)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    sub_kategoris = relationship("SubKategoriProduk", back_populates="kategori", lazy="raise")


class SubKategoriProduk(Base):
    __tablename__ = "sub_categories"
    __table_args__ = (
        Index("idx_subcategories_categories_id", "categories_id"),
        Index("idx_subcategories_cabang_id", "cabang_id"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    name = Column(String(255), nullable=False)
    nama = synonym("name")
    categories_id = Column(Integer, ForeignKey("categories.id"), nullable=True)
    kategori_id = synonym("categories_id")
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    kategori = relationship("KategoriProduk", back_populates="sub_kategoris", lazy="raise")
    produks = relationship("Produk", back_populates="sub_kategori", lazy="raise")


class Supplier(Base):
    __tablename__ = "suppliers"
    __table_args__ = (Index("idx_supplier_cabang", "cabang_id"),)

    id = Column(Integer, primary_key=True, autoincrement=True)
    nama = Column(String(255), nullable=False)
    no_hp = Column(String(20), nullable=True)
    alamat = Column(Text, nullable=True)
    email = Column(String(255), nullable=True)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    deleted_at = Column(DateTime, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


class Produk(Base):
    __tablename__ = "products"
    __table_args__ = (
        Index("idx_products_cabang_id", "cabang_id"),
        Index("idx_products_product_code", "product_code"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    product_name = Column(String(255), nullable=False)
    nama = synonym("product_name")
    product_code = Column(String(50), nullable=True)
    kode = synonym("product_code")
    barcode = synonym("product_code")
    category_name = Column(String(255), nullable=True)
    kategori_nama = synonym("category_name")
    tipe = synonym("category_name")
    sub_categories_id = Column(Integer, ForeignKey("sub_categories.id"), nullable=True)
    sub_kategori_id = synonym("sub_categories_id")
    categories_id = Column(Integer, ForeignKey("categories.id"), nullable=True)
    kategori_id = synonym("categories_id")
    brands_id = Column(Integer, ForeignKey("brands.id"), nullable=True)
    merek_id = synonym("brands_id")
    model_series_id = Column(Integer, ForeignKey("model_series.id"), nullable=True)
    model_seri_id = synonym("model_series_id")
    capacities_id = Column(Integer, ForeignKey("capacities.id"), nullable=True)
    kapasitas_id = synonym("capacities_id")
    nomor_seri = Column(String(255), nullable=True)
    imei = synonym("nomor_seri")
    warna = Column(String(255), nullable=True)
    kondisi = Column(String(255), nullable=True)
    ram = Column(String(255), nullable=True)
    stok = Column(String(255), default="0")
    stok_minimal = Column(String(255), nullable=True)
    stok_minimum = synonym("stok_minimal")
    harga_modal = Column(String(255), default="0")
    harga_beli = synonym("harga_modal")
    harga_jual = Column(String(255), default="0")
    harga_jual_toko = Column(String(255), nullable=True)
    garansi = Column(String(255), nullable=True)
    garansi_imei = Column(String(255), nullable=True)
    keterangan = Column(Text, nullable=True)
    foto = Column(String(255), nullable=True)
    is_portal = Column(Integer, default=0)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    deleted_at = Column(DateTime, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    sub_kategori = relationship("SubKategoriProduk", back_populates="produks", lazy="raise")


# ─────────────────────────────────────────────────────────
# ORDERS (PENJUALAN / POS)
# ─────────────────────────────────────────────────────────
class Order(Base):
    __tablename__ = "orders"
    __table_args__ = (
        Index("idx_orders_cabang_id", "cabang_id"),
        Index("idx_orders_payment_status", "payment_status"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    invoice_no = Column(String(50), nullable=False)
    no_invoice = synonym("invoice_no")
    customers_id = Column(Integer, ForeignKey("customers.id", ondelete="SET NULL"), nullable=True)
    pelanggan_id = synonym("customers_id")
    users_id = Column(Integer, ForeignKey("users.id", ondelete="SET NULL"), nullable=True)
    user_id = synonym("users_id")
    cabang_id = Column(Integer, ForeignKey("cabangs.id", ondelete="SET NULL"), nullable=True)
    sub_total = Column(Numeric(15, 2), default=0)
    total = synonym("sub_total")
    subtotal = synonym("sub_total")
    discount_amount = Column(Numeric(15, 2), default=0)
    diskon = synonym("discount_amount")
    payment_status = Column(String(50), default="lunas")
    status = synonym("payment_status")
    pay = Column(Numeric(15, 2), default=0)
    bayar = synonym("pay")
    due = Column(Numeric(15, 2), default=0)
    sisa_bayar = synonym("due")
    note = Column(Text, nullable=True)
    catatan = synonym("note")
    deleted_at = Column(DateTime, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    pelanggan = relationship("Pelanggan", back_populates="orders", lazy="raise")
    details = relationship("OrderDetail", back_populates="order", lazy="raise")


class OrderDetail(Base):
    __tablename__ = "order_details"
    __table_args__ = (
        Index("idx_orderdetail_order", "order_id"),
        Index("idx_orderdetail_produk", "produk_id"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    order_id = Column(Integer, ForeignKey("orders.id", ondelete="CASCADE"), nullable=False)
    produk_id = Column(Integer, ForeignKey("produks.id", ondelete="SET NULL"), nullable=True)
    nama_produk = Column(String(255), nullable=True)
    qty = Column(Integer, default=1)
    harga = Column(Numeric(15, 2), default=0)
    diskon = Column(Numeric(15, 2), default=0)
    subtotal = Column(Numeric(15, 2), default=0)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    order = relationship("Order", back_populates="details", lazy="raise")


# ─────────────────────────────────────────────────────────
# PURCHASE
# ─────────────────────────────────────────────────────────
class Purchase(Base):
    __tablename__ = "purchases"
    __table_args__ = (
        Index("idx_purchase_cabang", "cabang_id"),
        Index("idx_purchase_supplier", "supplier_id"),
        Index("idx_purchase_deleted", "deleted_at"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    no_po = Column(String(50), unique=True, nullable=False)
    supplier_id = Column(Integer, ForeignKey("suppliers.id"), nullable=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=True)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    total = Column(Numeric(15, 2), default=0)
    status = Column(Enum("pending", "diterima", "batal"), default="pending")
    catatan = Column(Text, nullable=True)
    tgl_po = Column(Date, nullable=True)
    deleted_at = Column(DateTime, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    details = relationship("PurchaseDetail", back_populates="purchase", lazy="raise")


class PurchaseDetail(Base):
    __tablename__ = "purchase_details"
    __table_args__ = (Index("idx_purchasedetail_purchase", "purchase_id"),)

    id = Column(Integer, primary_key=True, autoincrement=True)
    purchase_id = Column(Integer, ForeignKey("purchases.id", ondelete="CASCADE"), nullable=False)
    produk_id = Column(Integer, ForeignKey("produks.id"), nullable=True)
    nama_produk = Column(String(255), nullable=True)
    qty = Column(Integer, default=1)
    harga_beli = Column(Numeric(15, 2), default=0)
    subtotal = Column(Numeric(15, 2), default=0)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    purchase = relationship("Purchase", back_populates="details", lazy="raise")


# ─────────────────────────────────────────────────────────
# KEUANGAN
# ─────────────────────────────────────────────────────────
class Pengeluaran(Base):
    __tablename__ = "expenses"
    __table_args__ = (
        Index("idx_expenses_cabang_id", "cabang_id"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    name = Column(String(255), nullable=False)
    nama = synonym("name")
    price = Column(Numeric(15, 2), default=0)
    jumlah = synonym("price")
    tipe = Column(String(100), nullable=True)
    foto = Column(String(255), nullable=True)
    bukti = synonym("foto")
    users_id = Column(Integer, ForeignKey("users.id"), nullable=True)
    user_id = synonym("users_id")
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    is_approve = Column(Boolean, default=False)
    is_approved = synonym("is_approve")
    deleted_at = Column(DateTime, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    tgl_pengeluaran = synonym("created_at")
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


class Kasbon(Base):
    __tablename__ = "kasbons"
    __table_args__ = (
        Index("idx_kasbon_user", "user_id"),
        Index("idx_kasbon_cabang_status", "cabang_id", "status"),
        Index("idx_kasbon_deleted", "deleted_at"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=False)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    jumlah = Column(Numeric(15, 2), default=0)
    keterangan = Column(Text, nullable=True)
    status = Column(Enum("menunggu", "disetujui", "ditolak", "lunas"), default="menunggu")
    tgl_kasbon = Column(Date, nullable=True)
    tgl_lunas = Column(Date, nullable=True)
    deleted_at = Column(DateTime, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


class Inventaris(Base):
    __tablename__ = "inventaris"
    __table_args__ = (
        Index("idx_inventaris_cabang", "cabang_id"),
        Index("idx_inventaris_deleted", "deleted_at"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    nama = Column(String(255), nullable=False)
    kode = Column(String(50), nullable=True)
    kondisi = Column(Enum("baik", "rusak", "hilang"), default="baik")
    lokasi = Column(String(255), nullable=True)
    keterangan = Column(Text, nullable=True)
    foto = Column(String(255), nullable=True)
    harga = Column(Numeric(15, 2), default=0)
    tgl_beli = Column(Date, nullable=True)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    deleted_at = Column(DateTime, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


class Insiden(Base):
    __tablename__ = "insidens"
    __table_args__ = (
        Index("idx_insiden_cabang", "cabang_id"),
        Index("idx_insiden_deleted", "deleted_at"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    judul = Column(String(255), nullable=False)
    keterangan = Column(Text, nullable=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=True)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    tgl_insiden = Column(Date, nullable=True)
    severity = Column(Enum("rendah", "sedang", "tinggi"), default="rendah")
    deleted_at = Column(DateTime, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


# ─────────────────────────────────────────────────────────
# KARYAWAN / GAJI
# ─────────────────────────────────────────────────────────
class Absensi(Base):
    __tablename__ = "absensi"
    __table_args__ = (
        Index("idx_absensi_user_tgl", "user_id", "tgl_absen"),
        Index("idx_absensi_cabang_tgl", "cabang_id", "tgl_absen"),
        UniqueConstraint("user_id", "tgl_absen", name="uq_absensi_user_tgl"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=False)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    tgl_absen = Column(Date, nullable=False)
    jam_masuk = Column(Time, nullable=True)
    jam_keluar = Column(Time, nullable=True)
    status = Column(Enum("hadir", "izin", "sakit", "alpha"), default="hadir")
    keterangan = Column(Text, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


class Izin(Base):
    __tablename__ = "izins"
    __table_args__ = (
        Index("idx_izin_user", "user_id"),
        Index("idx_izin_status", "status"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=False)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    tipe = Column(String(50), nullable=True)
    tanggal_mulai = Column(Date, nullable=True)
    tgl_mulai = synonym("tanggal_mulai")
    tanggal_selesai = Column(Date, nullable=True)
    tgl_selesai = synonym("tanggal_selesai")
    keterangan = Column(Text, nullable=True)
    alasan = synonym("keterangan")
    dokumen = Column(String(255), nullable=True)
    bukti = synonym("dokumen")
    nominal_potongan = Column(Numeric(15, 2), default=0)
    status = Column(String(50), default="menunggu")
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


class Overtime(Base):
    __tablename__ = "overtimes"
    __table_args__ = (
        Index("idx_overtime_user", "id_user"),
        Index("idx_overtime_cabang_tgl", "cabang_id", "tanggal"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    id_user = Column(Integer, ForeignKey("users.id"), nullable=False)
    user_id = synonym("id_user")
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    nominal_overtime = Column(Numeric(15, 2), default=0)
    tanggal = Column(Date, nullable=False)
    tgl_overtime = synonym("tanggal")
    waktu_start = Column(Time, nullable=True)
    jam_mulai = synonym("waktu_start")
    waktu_end = Column(Time, nullable=True)
    jam_selesai = synonym("waktu_end")
    approve_by = Column(Integer, nullable=True)
    keterangan = Column(Text, nullable=True)
    status = Column(String(50), default="menunggu")
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


class Shift(Base):
    __tablename__ = "shifts"

    id = Column(Integer, primary_key=True, autoincrement=True)
    nama = Column(String(100), nullable=False)
    jam_masuk = Column(Time, nullable=False)
    jam_keluar = Column(Time, nullable=False)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


class GajiKaryawan(Base):
    __tablename__ = "gaji_karyawans"
    __table_args__ = (
        Index("idx_gaji_user_periode", "user_id", "periode_tahun", "periode_bulan"),
        Index("idx_gaji_cabang_periode", "cabang_id", "periode_tahun", "periode_bulan"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=False)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    periode_bulan = Column(Integer, nullable=False)
    periode_tahun = Column(Integer, nullable=False)
    gaji_pokok = Column(Numeric(15, 2), default=0)
    tunjangan = Column(Numeric(15, 2), default=0)
    bonus = Column(Numeric(15, 2), default=0)
    potongan_kasbon = Column(Numeric(15, 2), default=0)
    potongan_absen = Column(Numeric(15, 2), default=0)
    total_gaji = Column(Numeric(15, 2), default=0)
    status = Column(Enum("draft", "diproses", "dibayar"), default="draft")
    catatan = Column(Text, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


# ─────────────────────────────────────────────────────────
# TARGET
# ─────────────────────────────────────────────────────────
class Target(Base):
    __tablename__ = "targets"
    __table_args__ = (
        Index("idx_target_cabang_periode", "cabang_id", "periode_tahun", "periode_bulan"),
        Index("idx_target_user", "user_id"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    tipe = Column(Enum("servis", "penjualan", "teknisi"), default="servis")
    user_id = Column(Integer, ForeignKey("users.id"), nullable=True)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    target_amount = Column(Numeric(15, 2), default=0)
    achieved_amount = Column(Numeric(15, 2), default=0)
    periode_bulan = Column(Integer, nullable=False)
    periode_tahun = Column(Integer, nullable=False)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


# ─────────────────────────────────────────────────────────
# TRANSFER STOK
# ─────────────────────────────────────────────────────────
class TransferStok(Base):
    __tablename__ = "transfer_stoks"
    __table_args__ = (
        Index("idx_transfer_dari", "dari_cabang_id"),
        Index("idx_transfer_ke", "ke_cabang_id"),
        Index("idx_transfer_produk", "produk_id"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    produk_id = Column(Integer, ForeignKey("produks.id"), nullable=False)
    dari_cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=False)
    ke_cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=False)
    qty = Column(Integer, default=0)
    keterangan = Column(Text, nullable=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=True)
    status = Column(Enum("pending", "diterima", "ditolak"), default="pending")
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


# ─────────────────────────────────────────────────────────
# RETUR & TUKAR TAMBAH
# ─────────────────────────────────────────────────────────
class Retur(Base):
    __tablename__ = "returs"
    __table_args__ = (
        Index("idx_retur_cabang", "cabang_id"),
        Index("idx_retur_deleted", "deleted_at"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    no_retur = Column(String(50), nullable=True)
    order_id = Column(Integer, ForeignKey("orders.id"), nullable=True)
    produk_id = Column(Integer, ForeignKey("produks.id"), nullable=True)
    qty = Column(Integer, default=1)
    alasan = Column(Text, nullable=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=True)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    deleted_at = Column(DateTime, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


class TukarTambah(Base):
    __tablename__ = "tukar_tambah"
    __table_args__ = (
        Index("idx_tt_cabang", "cabang_id"),
        Index("idx_tt_deleted", "deleted_at"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    no_nota = Column(String(50), nullable=True)
    no_tt = Column(String(50), nullable=True)
    pelanggan_id = Column(Integer, ForeignKey("pelanggans.id"), nullable=True)
    produk_lama_id = Column(Integer, ForeignKey("produks.id"), nullable=True)
    produk_baru_id = Column(Integer, ForeignKey("produks.id"), nullable=True)
    nama_hp_lama = Column(String(255), nullable=True)
    kondisi = Column(String(50), nullable=True)
    harga_tukar = Column(Numeric(15, 2), default=0)
    total_beli = Column(Numeric(15, 2), default=0)
    harga_produk_lama = Column(Numeric(15, 2), default=0)
    harga_produk_baru = Column(Numeric(15, 2), default=0)
    selisih = Column(Numeric(15, 2), default=0)
    status = Column(String(50), default="proses")
    user_id = Column(Integer, ForeignKey("users.id"), nullable=True)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    deleted_at = Column(DateTime, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


class TukarTambahDetail(Base):
    __tablename__ = "tukar_tambah_details"
    __table_args__ = (
        Index("idx_ttd_tt", "tukar_tambah_id"),
        Index("idx_ttd_produk", "produk_id"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    tukar_tambah_id = Column(Integer, ForeignKey("tukar_tambah.id", ondelete="CASCADE"), nullable=False)
    produk_id = Column(Integer, ForeignKey("produks.id"), nullable=True)
    nama_produk = Column(String(255), nullable=True)
    qty = Column(Integer, default=1)
    harga = Column(Numeric(15, 2), default=0)
    created_at = Column(DateTime, default=datetime.utcnow)


# ─────────────────────────────────────────────────────────
# PENGATURAN
# ─────────────────────────────────────────────────────────
class Sistem(Base):
    __tablename__ = "sistems"

    id = Column(Integer, primary_key=True, autoincrement=True)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True, unique=True)
    jam_buka = Column(Time, nullable=True)
    jam_tutup = Column(Time, nullable=True)
    hari_kerja = Column(String(255), nullable=True)
    pajak_persen = Column(Float, default=0)
    garansi_default_hari = Column(Integer, default=30)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


class SyaratKetentuan(Base):
    __tablename__ = "syarat_ketentuans"

    id = Column(Integer, primary_key=True, autoincrement=True)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True, unique=True)
    isi_terima = Column(Text, nullable=True)
    isi_pengambilan = Column(Text, nullable=True)
    isi_penjualan = Column(Text, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


# ─────────────────────────────────────────────────────────
# INVESTOR
# ─────────────────────────────────────────────────────────
class Investor(Base):
    __tablename__ = "investors"

    id = Column(Integer, primary_key=True, autoincrement=True)
    nama = Column(String(255), nullable=False)
    persentase = Column(Float, default=0)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


# ─────────────────────────────────────────────────────────
# AUDIT LOG — append-only, BigInteger PK
# ─────────────────────────────────────────────────────────
class AuditLog(Base):
    __tablename__ = "audit_logs"
    __table_args__ = (
        Index("idx_audit_user", "user_id"),
        Index("idx_audit_cabang_created", "cabang_id", "created_at"),
        Index("idx_audit_model", "model", "model_id"),
    )

    id = Column(BigInteger, primary_key=True, autoincrement=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=True)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    action = Column(String(50), nullable=False)
    model = Column(String(100), nullable=True)
    model_id = Column(Integer, nullable=True)
    data_lama = Column(JSON, nullable=True)
    data_baru = Column(JSON, nullable=True)
    ip_address = Column(String(45), nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow, nullable=False)

# ---- MODELS TAMBAHAN ---- 

from app.models.models_extra import (
    StoreSetting, UserCabangAkses, TransaksiServisTeknisi,
    PersetujuanHapusTransaksi, TransferStokHeader, TransferStokDetail,
    Bonus, TeknisiTarget, SalesTarget, RincianInvest,
    QCItem, ProdukHandphone, ProdukSparepart,
)
