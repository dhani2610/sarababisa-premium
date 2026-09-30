"""
Models tambahan yang di-append ke models.py utama.
Import file ini di __init__.py atau di main models.py di bagian akhir.
"""
from sqlalchemy import (
    Column, Integer, String, Text, DateTime, Date,
    Boolean, Float, Numeric, ForeignKey, Enum, JSON, BigInteger,
    SmallInteger, Index, UniqueConstraint
)
from sqlalchemy.orm import relationship, synonym
from datetime import datetime
from app.models.models import Base


# ─────────────────────────────────────────────────────────
# STORE SETTING — per cabang
# ─────────────────────────────────────────────────────────
class StoreSetting(Base):
    __tablename__ = "store_settings"
    __table_args__ = (Index("idx_storesetting_cabang", "cabang_id"),)

    id = Column(Integer, primary_key=True, autoincrement=True)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True, unique=True)
    nama_toko = Column(String(255), nullable=True)
    alamat = Column(Text, nullable=True)
    no_hp = Column(String(20), nullable=True)
    logo = Column(String(255), nullable=True)
    is_tax = Column(Boolean, default=False)
    ppn = Column(Float, default=11)
    # Telegram
    telegram_token = Column(String(500), nullable=True)
    telegram_chat_id = Column(String(100), nullable=True)
    is_telegram = Column(Boolean, default=False)
    # Fonnte WA
    fonnte_token = Column(String(500), nullable=True)
    is_wa_pelanggan = Column(Boolean, default=False)
    is_wa_baru = Column(Boolean, default=False)
    # Setting transaksi
    approval_hapus_transaksi = Column(Boolean, default=False)
    is_edit_transaksi = Column(Boolean, default=True)
    is_profit_produk = Column(Boolean, default=True)
    is_modal_produk = Column(Boolean, default=False)
    # Gaji
    nominal_overtime = Column(Numeric(15, 2), default=0)
    nominal_potongan_izin = Column(Numeric(15, 2), default=0)
    nominal_potongan_alfa = Column(Numeric(15, 2), default=0)
    nominal_potongan_sakit = Column(Numeric(15, 2), default=0)
    # Nota
    header_nota = Column(Text, nullable=True)
    footer_nota = Column(Text, nullable=True)
    kop_surat = Column(Text, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


# ─────────────────────────────────────────────────────────
# USER MULTI CABANG AKSES
# ─────────────────────────────────────────────────────────
class UserCabangAkses(Base):
    __tablename__ = "user_cabang_akses"
    __table_args__ = (
        Index("idx_uca_user", "user_id"),
        Index("idx_uca_cabang", "cabang_id"),
        UniqueConstraint("user_id", "cabang_id", name="uq_user_cabang"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    user_id = Column(Integer, ForeignKey("users.id", ondelete="CASCADE"), nullable=False)
    cabang_id = Column(Integer, ForeignKey("cabangs.id", ondelete="CASCADE"), nullable=False)
    created_at = Column(DateTime, default=datetime.utcnow)


# ─────────────────────────────────────────────────────────
# MULTI TEKNISI PER SERVIS
# ─────────────────────────────────────────────────────────
class TransaksiServisTeknisi(Base):
    """Satu servis bisa dikerjakan banyak teknisi dengan rincian tindakan dan profit sharing."""
    __tablename__ = "transaksi_servis_teknisis"
    __table_args__ = (
        Index("idx_tst_servis", "transaksi_servis_id"),
        Index("idx_tst_teknisi", "teknisi_id"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    transaksi_servis_id = Column(Integer, ForeignKey("transaksi_serviss.id", ondelete="CASCADE"), nullable=False)
    teknisi_id = Column(Integer, ForeignKey("users.id", ondelete="CASCADE"), nullable=False)
    is_utama = Column(Boolean, default=False)
    tipe = Column(String(50), nullable=True)  # Hardware / Software / Interface
    modal_sparepart = Column(Numeric(15, 2), default=0)
    biaya = Column(Numeric(15, 2), default=0)
    profit = Column(Numeric(15, 2), default=0)
    profittoko = Column(Numeric(15, 2), default=0)
    bonus_interface = Column(Numeric(15, 2), default=0)
    persen_teknisi = Column(Integer, default=0)
    tindakan_servis = Column(JSON, nullable=True)
    service_actions = Column(JSON, nullable=True)
    products = Column(JSON, nullable=True)
    biaya_j = Column(JSON, nullable=True)
    modal_j = Column(JSON, nullable=True)
    garansi = Column(JSON, nullable=True)
    catatan = Column(Text, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


# ─────────────────────────────────────────────────────────
# PERSETUJUAN HAPUS TRANSAKSI
# ─────────────────────────────────────────────────────────
class PersetujuanHapusTransaksi(Base):
    __tablename__ = "persetujuan_hapus_transaksis"
    __table_args__ = (
        Index("idx_pht_cabang_status", "cabang_id", "status"),
        Index("idx_pht_user", "user_id"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    tipe = Column(String(50), nullable=False)     # servis | penjualan | purchase
    record_id = Column(Integer, nullable=False)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=True)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    alasan = Column(Text, nullable=True)
    status = Column(Enum("menunggu", "disetujui", "ditolak"), default="menunggu")
    approved_by = Column(Integer, ForeignKey("users.id"), nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


# ─────────────────────────────────────────────────────────
# TRANSFER STOK HEADER (multi produk)
# ─────────────────────────────────────────────────────────
class TransferStokHeader(Base):
    __tablename__ = "transfer_stok_headers"
    __table_args__ = (
        Index("idx_tsh_asal", "cabang_asal"),
        Index("idx_tsh_tujuan", "cabang_tujuan"),
        Index("idx_tsh_status", "status"),
        Index("idx_tsh_deleted", "deleted_at"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    no_transfer = Column(String(50), unique=True, nullable=False)
    cabang_asal = Column(Integer, ForeignKey("cabangs.id"), nullable=False)
    cabang_tujuan = Column(Integer, ForeignKey("cabangs.id"), nullable=False)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=True)
    catatan = Column(Text, nullable=True)
    status = Column(Enum("menunggu", "selesai", "ditolak"), default="menunggu")
    approved_by = Column(Integer, ForeignKey("users.id"), nullable=True)
    approved_at = Column(DateTime, nullable=True)
    deleted_at = Column(DateTime, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    details = relationship("TransferStokDetail", back_populates="header", lazy="raise")


class TransferStokDetail(Base):
    __tablename__ = "transfer_stok_details"
    __table_args__ = (
        Index("idx_tsd_header", "transfer_id"),
        Index("idx_tsd_produk", "produk_id"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    transfer_id = Column(Integer, ForeignKey("transfer_stok_headers.id", ondelete="CASCADE"), nullable=False)
    produk_id = Column(Integer, ForeignKey("produks.id"), nullable=True)
    nama_produk = Column(String(255), nullable=True)
    qty = Column(Integer, default=1)
    created_at = Column(DateTime, default=datetime.utcnow)

    header = relationship("TransferStokHeader", back_populates="details", lazy="raise")


# ─────────────────────────────────────────────────────────
# BONUS & TARGET TEKNISI / SALES
# ─────────────────────────────────────────────────────────
class Bonus(Base):
    __tablename__ = "bonus"
    __table_args__ = (Index("idx_bonus_user_cabang", "user_id", "cabang_id"),)

    id = Column(Integer, primary_key=True, autoincrement=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=False)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    nominal = Column(Numeric(15, 2), default=0)
    keterangan = Column(Text, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


class TeknisiTarget(Base):
    __tablename__ = "teknisi_targets"
    __table_args__ = (Index("idx_ttarget_user_cabang", "users_id", "cabang_id"),)

    id = Column(Integer, primary_key=True, autoincrement=True)
    users_id = Column(Integer, ForeignKey("users.id"), nullable=False)
    user_id = synonym("users_id")
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    tipe = Column(String(20), default="item")
    item = Column(Integer, nullable=True)
    nominal = Column(Numeric(15, 2), nullable=True)
    bonus_nominal = Column(Numeric(15, 2), default=0)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


class SalesTarget(Base):
    __tablename__ = "sales_targets"
    __table_args__ = (Index("idx_starget_user_cabang", "users_id", "cabang_id"),)

    id = Column(Integer, primary_key=True, autoincrement=True)
    users_id = Column(Integer, ForeignKey("users.id"), nullable=False)
    user_id = synonym("users_id")
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    tipe = Column(String(20), default="nominal")
    item = Column(Integer, nullable=True)
    nominal = Column(Numeric(15, 2), nullable=True)
    bonus_nominal = Column(Numeric(15, 2), default=0)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


# ─────────────────────────────────────────────────────────
# RINCIAN INVEST
# ─────────────────────────────────────────────────────────
class RincianInvest(Base):
    __tablename__ = "rincian_invests"
    __table_args__ = (Index("idx_ri_cabang", "cabang_id"),)

    id = Column(Integer, primary_key=True, autoincrement=True)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    nama = Column(String(255), nullable=False)
    nominal = Column(Numeric(15, 2), default=0)
    persentase = Column(Float, default=0)
    keterangan = Column(Text, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


# ─────────────────────────────────────────────────────────
# QC CHECK ITEMS
# ─────────────────────────────────────────────────────────
class QCItem(Base):
    __tablename__ = "qc_items"
    __table_args__ = (Index("idx_qcitem_cabang", "cabang_id"),)

    id = Column(Integer, primary_key=True, autoincrement=True)
    cabang_id = Column(Integer, ForeignKey("cabangs.id"), nullable=True)
    nama = Column(String(255), nullable=False)
    urutan = Column(Integer, default=0)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


# ─────────────────────────────────────────────────────────
# PRODUK ATRIBUT HANDPHONE & SPAREPART
# ─────────────────────────────────────────────────────────
class ProdukHandphone(Base):
    __tablename__ = "produk_handphones"
    __table_args__ = (Index("idx_ph_produk", "produk_id"),)

    id = Column(Integer, primary_key=True, autoincrement=True)
    produk_id = Column(Integer, ForeignKey("produks.id", ondelete="CASCADE"), nullable=False, unique=True)
    imei = Column(String(50), nullable=True)
    imei2 = Column(String(50), nullable=True)
    kapasitas = Column(String(50), nullable=True)
    warna = Column(String(100), nullable=True)
    tipe_os = Column(String(50), nullable=True)
    nomor_seri = Column(String(100), nullable=True)
    kondisi = Column(String(50), nullable=True)  # baru | second | refurb
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


class ProdukSparepart(Base):
    __tablename__ = "produk_spareparts"
    __table_args__ = (Index("idx_ps_produk", "produk_id"),)

    id = Column(Integer, primary_key=True, autoincrement=True)
    produk_id = Column(Integer, ForeignKey("produks.id", ondelete="CASCADE"), nullable=False, unique=True)
    kompatibel_merek = Column(String(255), nullable=True)
    kompatibel_model = Column(Text, nullable=True)  # JSON list
    kualitas = Column(String(50), nullable=True)     # ori | compatible | refurb
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)


# Convenience re-export untuk import *
__all__ = [
    "StoreSetting", "UserCabangAkses", "TransaksiServisTeknisi",
    "PersetujuanHapusTransaksi", "TransferStokHeader", "TransferStokDetail",
    "Bonus", "TeknisiTarget", "SalesTarget", "RincianInvest",
    "QCItem", "ProdukHandphone", "ProdukSparepart",
]

