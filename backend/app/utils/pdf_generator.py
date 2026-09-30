"""
PDF Generator — ReportLab implementation for Nota Servis & Nota Penjualan
Format:
1. Nota Termal (80mm printer thermal roll)
2. Nota Inkjet (A4 PDF resmi dengan kop toko, tabel rincian, tanda tangan & syarat ketentuan)
3. Nota QC (Form QC check sebelum unit diserahkan)
"""
import io
from datetime import datetime
from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.lib.units import mm
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, HRFlowable, KeepTogether
)
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_RIGHT, TA_JUSTIFY


def _rupiah(val) -> str:
    try:
        n = int(float(val or 0))
        return f"Rp {n:,.0f}".replace(",", ".")
    except Exception:
        return "Rp 0"


def generate_nota_termal(data: dict, store: dict) -> io.BytesIO:
    """
    Nota Tanda Terima / Pengambilan Servis Format Termal (80mm width).
    """
    buffer = io.BytesIO()
    page_width = 80 * mm
    # Dinamis perkiraan tinggi berdasarkan items
    extra_h = len(data.get("spareparts", [])) * 5 * mm + len(data.get("teknisis", [])) * 5 * mm
    page_height = (175 * mm) + extra_h

    doc = SimpleDocTemplate(
        buffer,
        pagesize=(page_width, page_height),
        leftMargin=3 * mm,
        rightMargin=3 * mm,
        topMargin=4 * mm,
        bottomMargin=4 * mm,
    )

    styles = getSampleStyleSheet()
    title_style = ParagraphStyle(
        'TermalTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=10,
        leading=12,
        alignment=TA_CENTER
    )
    store_style = ParagraphStyle(
        'TermalStore',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8,
        leading=10,
        alignment=TA_CENTER
    )
    bold_style = ParagraphStyle(
        'TermalBold',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8,
        leading=10
    )
    regular_style = ParagraphStyle(
        'TermalRegular',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8,
        leading=10
    )
    center_small = ParagraphStyle(
        'TermalSmall',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=7,
        leading=9,
        alignment=TA_CENTER
    )

    elements = []

    # Header Toko
    elements.append(Paragraph(data.get("tipe_nota", "TANDA TERIMA SERVIS"), title_style))
    elements.append(Paragraph(f"<b>{store.get('nama_toko', 'SARABABISA')}</b>", title_style))
    if store.get("alamat"):
        elements.append(Paragraph(store.get("alamat"), store_style))
    if store.get("no_hp"):
        elements.append(Paragraph(f"Telp/WA: {store.get('no_hp')}", store_style))

    elements.append(Spacer(1, 2 * mm))
    elements.append(HRFlowable(width="100%", thickness=0.8, color=colors.black, spaceBefore=1, spaceAfter=2))

    # Info Nota
    info_table_data = [
        [Paragraph("No. Servis", bold_style), Paragraph(f": {data.get('no_nota', '-')}", bold_style)],
        [Paragraph("Tanggal", regular_style), Paragraph(f": {data.get('tanggal', datetime.now().strftime('%d/%m/%Y %H:%M'))}", regular_style)],
        [Paragraph("Pelanggan", regular_style), Paragraph(f": {data.get('pelanggan_nama', '-')}", regular_style)],
        [Paragraph("No. HP", regular_style), Paragraph(f": {data.get('pelanggan_hp', '-')}", regular_style)],
        [Paragraph("Barang", regular_style), Paragraph(f": {data.get('nama_barang', '-')}", regular_style)],
        [Paragraph("IMEI / SN", regular_style), Paragraph(f": {data.get('imei', '-')}", regular_style)],
        [Paragraph("Kerusakan", regular_style), Paragraph(f": {data.get('kerusakan', '-')}", regular_style)],
    ]

    if data.get("estimasi_biaya") is not None:
        info_table_data.append([
            Paragraph("Est. Biaya", regular_style),
            Paragraph(f": {_rupiah(data.get('estimasi_biaya'))}", regular_style)
        ])
    if data.get("dp") is not None and float(data.get("dp", 0)) > 0:
        info_table_data.append([
            Paragraph("DP / Panjar", regular_style),
            Paragraph(f": {_rupiah(data.get('dp'))}", regular_style)
        ])
    if data.get("sisa_bayar") is not None:
        info_table_data.append([
            Paragraph("Sisa Bayar", bold_style),
            Paragraph(f": {_rupiah(data.get('sisa_bayar'))}", bold_style)
        ])
    if data.get("teknisi_nama"):
        info_table_data.append([
            Paragraph("Teknisi", regular_style),
            Paragraph(f": {data.get('teknisi_nama')}", regular_style)
        ])

    info_table = Table(info_table_data, colWidths=[24 * mm, 50 * mm])
    info_table.setStyle(TableStyle([
        ('VALIGN', (0, 0), (-1, -1), 'TOP'),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 1),
        ('TOPPADDING', (0, 0), (-1, -1), 1),
        ('LEFTPADDING', (0, 0), (-1, -1), 0),
        ('RIGHTPADDING', (0, 0), (-1, -1), 0),
    ]))
    elements.append(info_table)

    # Multi Teknisi jika ada
    teknisis = data.get("teknisis", [])
    if teknisis:
        elements.append(Spacer(1, 1 * mm))
        elements.append(Paragraph("<b>Tim Teknisi:</b>", bold_style))
        for t in teknisis:
            elements.append(Paragraph(f"• {t.get('nama')} ({t.get('tipe', 'Teknisi')})", regular_style))

    # Spareparts jika ada (Nota Pengambilan)
    spareparts = data.get("spareparts", [])
    if spareparts:
        elements.append(Spacer(1, 2 * mm))
        elements.append(HRFlowable(width="100%", thickness=0.5, color=colors.black, spaceBefore=1, spaceAfter=2))
        elements.append(Paragraph("<b>Rincian Sparepart/Biaya:</b>", bold_style))
        sp_data = []
        for sp in spareparts:
            sp_data.append([
                Paragraph(f"{sp.get('nama', '-')} x{sp.get('qty', 1)}", regular_style),
                Paragraph(_rupiah(sp.get('subtotal', 0)), regular_style)
            ])
        sp_table = Table(sp_data, colWidths=[50 * mm, 24 * mm])
        sp_table.setStyle(TableStyle([
            ('ALIGN', (1, 0), (1, -1), 'RIGHT'),
            ('BOTTOMPADDING', (0, 0), (-1, -1), 1),
            ('TOPPADDING', (0, 0), (-1, -1), 1),
            ('LEFTPADDING', (0, 0), (-1, -1), 0),
            ('RIGHTPADDING', (0, 0), (-1, -1), 0),
        ]))
        elements.append(sp_table)

    elements.append(Spacer(1, 2 * mm))
    elements.append(HRFlowable(width="100%", thickness=0.8, color=colors.black, spaceBefore=1, spaceAfter=2))

    # Footer
    elements.append(Paragraph(f"Dicetak oleh: {data.get('petugas', 'Admin')}", center_small))
    elements.append(Paragraph(f"[{datetime.now().strftime('%d/%m/%Y %H:%M')}]", center_small))
    if store.get("rekening_info"):
        elements.append(Paragraph(store.get("rekening_info"), center_small))
    elements.append(Spacer(1, 1 * mm))
    elements.append(Paragraph("Harap bawa nota ini saat pengambilan barang.", center_small))
    elements.append(Paragraph("Terima kasih atas kepercayaan Anda.", center_small))

    doc.build(elements)
    buffer.seek(0)
    return buffer


def generate_nota_inkjet(data: dict, store: dict) -> io.BytesIO:
    """
    Nota Servis Format Inkjet (A4 PDF resmi dengan Kop Toko, Tabel Rincian, Tanda Tangan).
    """
    buffer = io.BytesIO()
    doc = SimpleDocTemplate(
        buffer,
        pagesize=A4,
        leftMargin=12 * mm,
        rightMargin=12 * mm,
        topMargin=10 * mm,
        bottomMargin=10 * mm,
    )

    styles = getSampleStyleSheet()
    h1 = ParagraphStyle('InkjetH1', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=15, leading=18, alignment=TA_CENTER)
    h2 = ParagraphStyle('InkjetH2', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=12, leading=15, alignment=TA_CENTER)
    desc_style = ParagraphStyle('InkjetDesc', parent=styles['Normal'], fontName='Helvetica', fontSize=9, leading=12, alignment=TA_CENTER)
    cell_bold = ParagraphStyle('InkjetCellB', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=9, leading=12)
    cell_reg = ParagraphStyle('InkjetCellR', parent=styles['Normal'], fontName='Helvetica', fontSize=9, leading=12)
    cell_right = ParagraphStyle('InkjetCellRt', parent=styles['Normal'], fontName='Helvetica', fontSize=9, leading=12, alignment=TA_RIGHT)
    terms_style = ParagraphStyle('InkjetTerms', parent=styles['Normal'], fontName='Helvetica', fontSize=7.5, leading=10, alignment=TA_JUSTIFY)

    elements = []

    # 1. KOP TOKO
    kop_data = [
        [
            Paragraph(f"<b>{store.get('nama_toko', 'SARABABISA PREMIUM')}</b>", h1),
        ],
        [
            Paragraph(f"{store.get('alamat', '')} • Telp/WA: {store.get('no_hp', '')}", desc_style),
        ]
    ]
    kop_table = Table(kop_data, colWidths=[186 * mm])
    elements.append(kop_table)
    elements.append(Spacer(1, 2 * mm))
    elements.append(HRFlowable(width="100%", thickness=1.5, color=colors.HexColor("#1A202C"), spaceBefore=1, spaceAfter=1))
    elements.append(HRFlowable(width="100%", thickness=0.5, color=colors.HexColor("#1A202C"), spaceBefore=1, spaceAfter=4))

    # 2. JUDUL NOTA & META
    judul = data.get("tipe_nota", "NOTA TANDA TERIMA SERVIS")
    elements.append(Paragraph(judul, h2))
    elements.append(Spacer(1, 3 * mm))

    meta_table_data = [
        [
            Paragraph(f"<b>No. Servis:</b> {data.get('no_nota', '-')}", cell_bold),
            Paragraph(f"<b>Tanggal Masuk:</b> {data.get('tanggal', datetime.now().strftime('%d/%m/%Y'))}", cell_reg),
            Paragraph(f"<b>Petugas:</b> {data.get('petugas', 'Admin')}", cell_right),
        ],
        [
            Paragraph(f"<b>Pelanggan:</b> {data.get('pelanggan_nama', '-')}", cell_reg),
            Paragraph(f"<b>No. HP/WA:</b> {data.get('pelanggan_hp', '-')}", cell_reg),
            Paragraph(f"<b>Status:</b> {data.get('status', 'proses').upper()}", cell_right),
        ]
    ]
    meta_table = Table(meta_table_data, colWidths=[65 * mm, 65 * mm, 56 * mm])
    meta_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, -1), colors.HexColor("#F7FAFC")),
        ('BOX', (0, 0), (-1, -1), 0.5, colors.HexColor("#CBD5E0")),
        ('INNERGRID', (0, 0), (-1, -1), 0.5, colors.HexColor("#E2E8F0")),
        ('TOPPADDING', (0, 0), (-1, -1), 4),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 4),
        ('LEFTPADDING', (0, 0), (-1, -1), 6),
        ('RIGHTPADDING', (0, 0), (-1, -1), 6),
    ]))
    elements.append(meta_table)
    elements.append(Spacer(1, 4 * mm))

    # 3. DETAIL BARANG & KERUSAKAN
    barang_data = [
        [Paragraph("<b>Informasi Perangkat</b>", cell_bold), Paragraph("<b>Detail</b>", cell_bold)],
        [Paragraph("Nama Perangkat", cell_reg), Paragraph(data.get("nama_barang", "-"), cell_reg)],
        [Paragraph("IMEI / No. Seri", cell_reg), Paragraph(data.get("imei", "-"), cell_reg)],
        [Paragraph("Warna / Kapasitas", cell_reg), Paragraph(f"{data.get('warna', '-')} / {data.get('kapasitas', '-')}", cell_reg)],
        [Paragraph("Keluhan / Kerusakan", cell_reg), Paragraph(data.get("kerusakan", "-"), cell_reg)],
        [Paragraph("Catatan / Kondisi Awal", cell_reg), Paragraph(data.get("catatan", "-"), cell_reg)],
    ]
    if data.get("teknisi_nama"):
        barang_data.append([Paragraph("Teknisi Penanggung Jawab", cell_reg), Paragraph(data.get("teknisi_nama"), cell_reg)])

    # Multi Teknisi
    teknisis = data.get("teknisis", [])
    if teknisis:
        tk_str = ", ".join([f"{t.get('nama')} ({t.get('tipe', 'Teknisi')})" for t in teknisis])
        barang_data.append([Paragraph("Tim Teknisi Bersama", cell_reg), Paragraph(tk_str, cell_reg)])

    barang_table = Table(barang_data, colWidths=[45 * mm, 141 * mm])
    barang_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor("#EDF2F7")),
        ('BOX', (0, 0), (-1, -1), 0.5, colors.HexColor("#CBD5E0")),
        ('INNERGRID', (0, 0), (-1, -1), 0.5, colors.HexColor("#E2E8F0")),
        ('TOPPADDING', (0, 0), (-1, -1), 3),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 3),
        ('LEFTPADDING', (0, 0), (-1, -1), 6),
        ('RIGHTPADDING', (0, 0), (-1, -1), 6),
    ]))
    elements.append(barang_table)
    elements.append(Spacer(1, 4 * mm))

    # 4. RINCIAN BIAYA & TINDAKAN / SPAREPART
    spareparts = data.get("spareparts", [])
    biaya_data = [
        [
            Paragraph("<b>No</b>", cell_bold),
            Paragraph("<b>Deskripsi Tindakan / Sparepart</b>", cell_bold),
            Paragraph("<b>Qty</b>", cell_bold),
            Paragraph("<b>Harga Satuan</b>", cell_bold),
            Paragraph("<b>Subtotal</b>", cell_bold),
        ]
    ]

    if spareparts:
        for idx, sp in enumerate(spareparts, 1):
            biaya_data.append([
                Paragraph(str(idx), cell_reg),
                Paragraph(sp.get("nama", "-"), cell_reg),
                Paragraph(str(sp.get("qty", 1)), cell_reg),
                Paragraph(_rupiah(sp.get("harga", 0)), cell_right),
                Paragraph(_rupiah(sp.get("subtotal", 0)), cell_right),
            ])
    else:
        biaya_data.append([
            Paragraph("1", cell_reg),
            Paragraph(f"Estimasi Biaya Servis ({data.get('kerusakan', 'Perbaikan')})", cell_reg),
            Paragraph("1", cell_reg),
            Paragraph(_rupiah(data.get("estimasi_biaya", 0)), cell_right),
            Paragraph(_rupiah(data.get("estimasi_biaya", 0)), cell_right),
        ])

    # Baris Summary (Total, DP, Sisa)
    total = float(data.get("total_biaya") or data.get("estimasi_biaya") or 0)
    dp = float(data.get("dp", 0))
    sisa = float(data.get("sisa_bayar", total - dp))

    biaya_data.append([
        "", "", "",
        Paragraph("<b>TOTAL BIAYA</b>", cell_bold),
        Paragraph(f"<b>{_rupiah(total)}</b>", cell_right),
    ])
    if dp > 0:
        biaya_data.append([
            "", "", "",
            Paragraph("Uang Muka (DP)", cell_reg),
            Paragraph(f"- {_rupiah(dp)}", cell_right),
        ])
    biaya_data.append([
        "", "", "",
        Paragraph("<b>SISA HARUS DIBAYAR</b>", cell_bold),
        Paragraph(f"<b>{_rupiah(sisa)}</b>", cell_right),
    ])

    biaya_table = Table(biaya_data, colWidths=[10 * mm, 90 * mm, 16 * mm, 35 * mm, 35 * mm])
    biaya_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor("#EDF2F7")),
        ('BOX', (0, 0), (-1, -1), 0.5, colors.HexColor("#CBD5E0")),
        ('INNERGRID', (0, 0), (-1, -1), 0.5, colors.HexColor("#E2E8F0")),
        ('ALIGN', (0, 0), (0, -1), 'CENTER'),
        ('ALIGN', (2, 0), (2, -1), 'CENTER'),
        ('ALIGN', (3, 0), (-1, -1), 'RIGHT'),
        ('TOPPADDING', (0, 0), (-1, -1), 3),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 3),
        ('LEFTPADDING', (0, 0), (-1, -1), 5),
        ('RIGHTPADDING', (0, 0), (-1, -1), 5),
    ]))
    elements.append(biaya_table)
    elements.append(Spacer(1, 4 * mm))

    # 5. SYARAT & KETENTUAN
    syarat_text = store.get("syarat_ketentuan") or (
        "1. Pengambilan barang wajib membawa dan menyerahkan nota tanda terima asli ini.<br/>"
        "2. Barang yang tidak diambil dalam waktu 30 (tiga puluh) hari kalender sejak status siap diambil, resiko kehilangan/kerusakan di luar tanggung jawab toko.<br/>"
        "3. Garansi berlaku sesuai ketentuan segel garansi tidak rusak, tidak terkena air, dan bukan akibat kesalahan pemakaian/jatuh.<br/>"
        "4. Toko tidak bertanggung jawab atas data yang hilang/terhapus selama proses pengerjaan (mohon backup data penting Anda sebelum servis)."
    )
    elements.append(Paragraph("<b>Syarat & Ketentuan Servis:</b>", cell_bold))
    elements.append(Paragraph(syarat_text, terms_style))
    elements.append(Spacer(1, 4 * mm))

    # 6. TANDA TANGAN
    sig_data = [
        [
            Paragraph("Pelanggan,", cell_bold),
            Paragraph("Hormat Kami / Toko,", cell_bold),
        ],
        [
            Spacer(1, 14 * mm),
            Spacer(1, 14 * mm),
        ],
        [
            Paragraph(f"( {data.get('pelanggan_nama', '....................')} )", cell_reg),
            Paragraph(f"( {data.get('petugas', store.get('nama_toko', 'Petugas'))} )", cell_reg),
        ]
    ]
    sig_table = Table(sig_data, colWidths=[93 * mm, 93 * mm])
    sig_table.setStyle(TableStyle([
        ('ALIGN', (0, 0), (-1, -1), 'CENTER'),
        ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
        ('LEFTPADDING', (0, 0), (-1, -1), 0),
        ('RIGHTPADDING', (0, 0), (-1, -1), 0),
    ]))
    elements.append(sig_table)

    doc.build(elements)
    buffer.seek(0)
    return buffer


def generate_nota_qc(data: dict, store: dict) -> io.BytesIO:
    """
    Form Pemeriksaan Kualitas (QC Check Sheet) A4 PDF.
    """
    buffer = io.BytesIO()
    doc = SimpleDocTemplate(
        buffer,
        pagesize=A4,
        leftMargin=12 * mm,
        rightMargin=12 * mm,
        topMargin=10 * mm,
        bottomMargin=10 * mm,
    )

    styles = getSampleStyleSheet()
    h1 = ParagraphStyle('QCH1', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=14, alignment=TA_CENTER)
    h2 = ParagraphStyle('QCH2', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=11, alignment=TA_CENTER)
    cell_bold = ParagraphStyle('QCCellB', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=8.5, leading=11)
    cell_reg = ParagraphStyle('QCCellR', parent=styles['Normal'], fontName='Helvetica', fontSize=8.5, leading=11)

    elements = []

    # Header
    elements.append(Paragraph(f"<b>{store.get('nama_toko', 'SARABABISA PREMIUM')}</b>", h1))
    elements.append(Paragraph("LEMBAR QUALITY CONTROL (QC) SERVIS PERANGKAT", h2))
    elements.append(Spacer(1, 2 * mm))
    elements.append(HRFlowable(width="100%", thickness=1, color=colors.black, spaceBefore=1, spaceAfter=4))

    # Meta
    meta = [
        [
            Paragraph(f"<b>No. Servis:</b> {data.get('no_nota', '-')}", cell_bold),
            Paragraph(f"<b>Pelanggan:</b> {data.get('pelanggan_nama', '-')}", cell_reg),
            Paragraph(f"<b>Tanggal:</b> {datetime.now().strftime('%d/%m/%Y')}", cell_reg),
        ],
        [
            Paragraph(f"<b>Perangkat:</b> {data.get('nama_barang', '-')}", cell_reg),
            Paragraph(f"<b>IMEI:</b> {data.get('imei', '-')}", cell_reg),
            Paragraph(f"<b>Teknisi:</b> {data.get('teknisi_nama', '-')}", cell_reg),
        ]
    ]
    elements.append(Table(meta, colWidths=[62 * mm, 62 * mm, 62 * mm]))
    elements.append(Spacer(1, 3 * mm))

    # Checklist Items
    qc_items = data.get("qc_items") or [
        "Layar LCD / Tampilan", "Fungsi Touchscreen", "Kamera Depan", "Kamera Belakang",
        "Speaker Atas (Earpiece)", "Speaker Musik / Bawah", "Mikrofon (Mic Telp & Rekam)",
        "Port Charging / Pengisian Daya", "Tombol Volume & Power", "Jaringan Seluler / SIM",
        "Koneksi WiFi & Bluetooth", "Sensor Sidik Jari (Fingerprint)", "Face ID / Kamera Sensor",
        "Baterai & Persentase Daya", "Getar (Vibrator)", "Body / Fisik Casing"
    ]

    qc_table_data = [
        [
            Paragraph("<b>No</b>", cell_bold),
            Paragraph("<b>Item Pengecekan</b>", cell_bold),
            Paragraph("<b>Kondisi Masuk</b>", cell_bold),
            Paragraph("<b>Kondisi Keluar (QC)</b>", cell_bold),
            Paragraph("<b>Keterangan</b>", cell_bold),
        ]
    ]

    for idx, item in enumerate(qc_items, 1):
        qc_table_data.append([
            Paragraph(str(idx), cell_reg),
            Paragraph(item, cell_reg),
            Paragraph("[  ] Normal   [  ] Rusak", cell_reg),
            Paragraph("[  ] Normal   [  ] Rusak", cell_reg),
            Paragraph("........................", cell_reg),
        ])

    qc_table = Table(qc_table_data, colWidths=[10 * mm, 65 * mm, 38 * mm, 38 * mm, 35 * mm])
    qc_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor("#EDF2F7")),
        ('BOX', (0, 0), (-1, -1), 0.5, colors.HexColor("#CBD5E0")),
        ('INNERGRID', (0, 0), (-1, -1), 0.5, colors.HexColor("#E2E8F0")),
        ('ALIGN', (0, 0), (0, -1), 'CENTER'),
        ('TOPPADDING', (0, 0), (-1, -1), 2.5),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 2.5),
        ('LEFTPADDING', (0, 0), (-1, -1), 4),
        ('RIGHTPADDING', (0, 0), (-1, -1), 4),
    ]))
    elements.append(qc_table)
    elements.append(Spacer(1, 5 * mm))

    # Tanda Tangan QC
    sig_data = [
        [
            Paragraph("Diperiksa Oleh (Teknisi/QC):", cell_bold),
            Paragraph("Diterima & Disetujui (Pelanggan):", cell_bold),
        ],
        [
            Spacer(1, 12 * mm),
            Spacer(1, 12 * mm),
        ],
        [
            Paragraph(f"( {data.get('teknisi_nama', '....................')} )", cell_reg),
            Paragraph(f"( {data.get('pelanggan_nama', '....................')} )", cell_reg),
        ]
    ]
    sig_table = Table(sig_data, colWidths=[93 * mm, 93 * mm])
    sig_table.setStyle(TableStyle([
        ('ALIGN', (0, 0), (-1, -1), 'CENTER'),
        ('LEFTPADDING', (0, 0), (-1, -1), 0),
        ('RIGHTPADDING', (0, 0), (-1, -1), 0),
    ]))
    elements.append(sig_table)

    doc.build(elements)
    buffer.seek(0)
    return buffer


def generate_nota_penjualan_termal(order_data: dict, store: dict) -> io.BytesIO:
    """
    Nota Penjualan POS Format Termal 80mm.
    """
    buffer = io.BytesIO()
    page_width = 80 * mm
    items = order_data.get("items", [])
    extra_h = len(items) * 6 * mm
    page_height = (140 * mm) + extra_h

    doc = SimpleDocTemplate(
        buffer,
        pagesize=(page_width, page_height),
        leftMargin=3 * mm,
        rightMargin=3 * mm,
        topMargin=4 * mm,
        bottomMargin=4 * mm,
    )

    styles = getSampleStyleSheet()
    title_style = ParagraphStyle('PSTitle', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=10, leading=12, alignment=TA_CENTER)
    store_style = ParagraphStyle('PSStore', parent=styles['Normal'], fontName='Helvetica', fontSize=8, leading=10, alignment=TA_CENTER)
    bold_style = ParagraphStyle('PSBold', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=8, leading=10)
    reg_style = ParagraphStyle('PSReg', parent=styles['Normal'], fontName='Helvetica', fontSize=8, leading=10)
    right_style = ParagraphStyle('PSRt', parent=styles['Normal'], fontName='Helvetica', fontSize=8, leading=10, alignment=TA_RIGHT)
    center_small = ParagraphStyle('PSSmall', parent=styles['Normal'], fontName='Helvetica', fontSize=7, leading=9, alignment=TA_CENTER)

    elements = []

    # Kop Toko
    elements.append(Paragraph(f"<b>{store.get('nama_toko', 'SARABABISA')}</b>", title_style))
    if store.get("alamat"):
        elements.append(Paragraph(store.get("alamat"), store_style))
    if store.get("no_hp"):
        elements.append(Paragraph(f"Telp/WA: {store.get('no_hp')}", store_style))
    elements.append(Spacer(1, 2 * mm))
    elements.append(HRFlowable(width="100%", thickness=0.8, color=colors.black, spaceBefore=1, spaceAfter=2))

    # Meta
    meta = [
        [Paragraph(f"No: {order_data.get('invoice_no', '-')}", reg_style), Paragraph(order_data.get("tanggal", datetime.now().strftime("%d/%m/%y %H:%M")), right_style)],
        [Paragraph(f"Kasir: {order_data.get('kasir', '-')}", reg_style), Paragraph(f"Cust: {order_data.get('pelanggan_nama', 'Umum')}", right_style)],
    ]
    elements.append(Table(meta, colWidths=[37 * mm, 37 * mm]))
    elements.append(HRFlowable(width="100%", thickness=0.5, color=colors.black, spaceBefore=1, spaceAfter=2))

    # Items
    item_rows = []
    for it in items:
        item_rows.append([
            Paragraph(f"{it.get('nama_produk', '-')}", reg_style),
            Paragraph(f"{it.get('qty', 1)} x {_rupiah(it.get('harga', 0))}", reg_style),
            Paragraph(_rupiah(it.get('total', 0)), right_style),
        ])
    elements.append(Table(item_rows, colWidths=[34 * mm, 24 * mm, 16 * mm]))
    elements.append(HRFlowable(width="100%", thickness=0.5, color=colors.black, spaceBefore=1, spaceAfter=2))

    # Summary
    subtotal = float(order_data.get("sub_total", 0))
    diskon = float(order_data.get("diskon", 0))
    total = float(order_data.get("total", subtotal - diskon))
    bayar = float(order_data.get("bayar", total))
    kembali = float(order_data.get("kembali", bayar - total))

    sum_rows = [
        [Paragraph("Subtotal", reg_style), Paragraph(_rupiah(subtotal), right_style)],
    ]
    if diskon > 0:
        sum_rows.append([Paragraph("Diskon", reg_style), Paragraph(f"- {_rupiah(diskon)}", right_style)])
    sum_rows.append([Paragraph("<b>TOTAL</b>", bold_style), Paragraph(f"<b>{_rupiah(total)}</b>", right_style)])
    sum_rows.append([Paragraph(f"Bayar ({order_data.get('metode_pembayaran', 'Tunai')})", reg_style), Paragraph(_rupiah(bayar), right_style)])
    sum_rows.append([Paragraph("Kembali", reg_style), Paragraph(_rupiah(kembali), right_style)])

    elements.append(Table(sum_rows, colWidths=[40 * mm, 34 * mm]))
    elements.append(Spacer(1, 2 * mm))
    elements.append(HRFlowable(width="100%", thickness=0.8, color=colors.black, spaceBefore=1, spaceAfter=2))

    elements.append(Paragraph("Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.", center_small))
    elements.append(Paragraph("Terima Kasih atas Kunjungan Anda!", center_small))

    doc.build(elements)
    buffer.seek(0)
    return buffer
