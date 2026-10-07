"""
Karyawan / SDM API — Gaji, Bonus, Absensi, Izin, Overtime, Shift
Multi-cabang: semua difilter per cabang_id.
"""
from fastapi import APIRouter, Depends, Query, HTTPException
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func, and_, update, extract
from typing import Optional
from datetime import datetime, date

from app.core.database import get_db
from app.core.security import require_kepala_toko, require_any
from app.models.models import (
    User, Bonus, Absensi, Izin, Overtime, Shift, StoreSetting
)
from app.utils.query import paginate

router = APIRouter()


# ─── Shift ────────────────────────────────────────────────
@router.get("/shift")
async def list_shift(db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    r = await db.execute(select(Shift).where(Shift.cabang_id == cu.cabang_id).order_by(Shift.nama))
    return r.scalars().all()


@router.post("/shift", status_code=201)
async def create_shift(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    s = Shift(nama=body["nama"], jam_masuk=body.get("jam_masuk"), jam_pulang=body.get("jam_pulang"), cabang_id=cu.cabang_id)
    db.add(s)
    await db.flush()
    return {"id": s.id}


@router.put("/shift/{id}")
async def update_shift(id: int, body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    await db.execute(update(Shift).where(Shift.id == id, Shift.cabang_id == cu.cabang_id).values(**{k: v for k, v in body.items() if k != "id"}))
    return {"message": "Shift diperbarui"}


@router.delete("/shift/{id}")
async def delete_shift(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    await db.execute(update(Shift).where(Shift.id == id).values(deleted_at=datetime.utcnow()))
    return {"message": "Shift dihapus"}


# ─── Absensi ──────────────────────────────────────────────
@router.get("/master/absensi")
async def list_absensi(
    page: int = Query(1), per_page: int = Query(30),
    bulan: Optional[str] = Query(None),
    user_id: Optional[int] = Query(None),
    db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko),
):
    base = and_(Absensi.cabang_id == cu.cabang_id)
    q = select(
        Absensi.id, Absensi.user_id, Absensi.tgl, Absensi.jam_masuk,
        Absensi.jam_pulang, Absensi.status, Absensi.keterangan,
        User.nama.label("nama_karyawan"),
    ).outerjoin(User, User.id == Absensi.user_id).where(base).order_by(Absensi.tgl.desc())
    cq = select(func.count(Absensi.id)).where(base)

    if bulan:
        try:
            dt = datetime.strptime(bulan, "%Y-%m")
            q = q.where(extract("year", Absensi.tgl) == dt.year, extract("month", Absensi.tgl) == dt.month)
            cq = cq.where(extract("year", Absensi.tgl) == dt.year, extract("month", Absensi.tgl) == dt.month)
        except ValueError:
            pass
    if user_id:
        q = q.where(Absensi.user_id == user_id)
        cq = cq.where(Absensi.user_id == user_id)

    return await paginate(db, q, cq, page, per_page)


@router.post("/master/absensi", status_code=201)
async def create_absensi(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    a = Absensi(
        user_id=body["user_id"],
        cabang_id=cu.cabang_id,
        tgl=body.get("tgl", date.today()),
        jam_masuk=body.get("jam_masuk"),
        jam_pulang=body.get("jam_pulang"),
        status=body.get("status", "hadir"),
        keterangan=body.get("keterangan"),
    )
    db.add(a)
    await db.flush()
    return {"id": a.id}


@router.delete("/master/absensi/{id}")
async def delete_absensi(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    from sqlalchemy import delete as sql_delete
    await db.execute(sql_delete(Absensi).where(Absensi.id == id, Absensi.cabang_id == cu.cabang_id))
    return {"message": "Absensi dihapus"}


# ─── Izin ────────────────────────────────────────────────
@router.get("/master/izin")
async def list_izin(
    page: int = Query(1), per_page: int = Query(15),
    db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko),
):
    base = Izin.cabang_id == cu.cabang_id
    q = select(Izin.id, Izin.user_id, Izin.tgl_mulai, Izin.tgl_selesai,
               Izin.jenis, Izin.alasan, Izin.status, Izin.created_at,
               User.nama.label("nama_karyawan")).outerjoin(User, User.id == Izin.user_id).where(base).order_by(Izin.created_at.desc())
    cq = select(func.count(Izin.id)).where(base)
    return await paginate(db, q, cq, page, per_page)


@router.post("/master/izin", status_code=201)
async def create_izin(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    izin = Izin(
        user_id=cu.id,
        cabang_id=cu.cabang_id,
        tgl_mulai=body["tgl_mulai"],
        tgl_selesai=body.get("tgl_selesai", body["tgl_mulai"]),
        jenis=body.get("jenis", "izin"),
        alasan=body.get("alasan"),
        status="menunggu",
    )
    db.add(izin)
    await db.flush()
    return {"id": izin.id}


@router.put("/master/izin/{id}")
async def update_izin(id: int, body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    await db.execute(update(Izin).where(Izin.id == id).values(status=body.get("status"), updated_at=datetime.utcnow()))
    return {"message": "Status izin diperbarui"}


@router.delete("/master/izin/{id}")
async def delete_izin(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    from sqlalchemy import delete as sql_delete
    await db.execute(sql_delete(Izin).where(Izin.id == id))
    return {"message": "Izin dihapus"}


# ─── Overtime ─────────────────────────────────────────────
@router.get("/master/overtime")
async def list_overtime(
    page: int = Query(1), per_page: int = Query(15),
    db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko),
):
    base = Overtime.cabang_id == cu.cabang_id
    q = select(Overtime.id, Overtime.user_id, Overtime.tgl, Overtime.jam,
               Overtime.keterangan, Overtime.status, Overtime.nominal, Overtime.created_at,
               User.nama.label("nama_karyawan")).outerjoin(User, User.id == Overtime.user_id).where(base).order_by(Overtime.created_at.desc())
    cq = select(func.count(Overtime.id)).where(base)
    return await paginate(db, q, cq, page, per_page)


@router.post("/master/overtime", status_code=201)
async def create_overtime(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_any)):
    """Karyawan bisa ajukan overtime sendiri."""
    r = await db.execute(select(StoreSetting.nominal_overtime).where(StoreSetting.cabang_id == cu.cabang_id))
    nominal_per_jam = float(r.scalar_one_or_none() or 0)
    jam = float(body.get("jam", 0))
    nominal = nominal_per_jam * jam

    ov = Overtime(
        user_id=cu.id, cabang_id=cu.cabang_id,
        tgl=body.get("tgl", date.today()),
        jam=jam, keterangan=body.get("keterangan"),
        nominal=nominal, status="menunggu",
    )
    db.add(ov)
    await db.flush()
    return {"id": ov.id, "nominal": nominal}


@router.post("/master/overtime/batch/approve")
async def approve_overtime_batch(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    await db.execute(update(Overtime).where(Overtime.id.in_(body.get("ids", []))).values(status="disetujui"))
    return {"message": "Overtime batch disetujui"}


@router.post("/master/overtime/batch/reject")
async def reject_overtime_batch(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    await db.execute(update(Overtime).where(Overtime.id.in_(body.get("ids", []))).values(status="ditolak"))
    return {"message": "Overtime batch ditolak"}


@router.post("/master/overtime/{id}/approve")
async def approve_overtime(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    await db.execute(update(Overtime).where(Overtime.id == id).values(status="disetujui", updated_at=datetime.utcnow()))
    return {"message": "Overtime disetujui"}


# ─── Gaji Karyawan ────────────────────────────────────────
@router.get("/gaji/karyawan")
async def list_karyawan(
    page: int = Query(1), per_page: int = Query(15),
    search: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko),
):
    """List karyawan + info gaji pokok."""
    base = and_(User.cabang_id == cu.cabang_id, User.deleted_at.is_(None), User.id != 1)
    q = select(
        User.id, User.nama, User.username, User.role,
        User.gaji_pokok, User.shift_id, User.is_active,
        Shift.nama.label("shift_nama"),
    ).outerjoin(Shift, Shift.id == User.shift_id).where(base).order_by(User.nama)
    cq = select(func.count(User.id)).where(base)

    if search:
        t = f"%{search}%"
        q = q.where(User.nama.ilike(t))
        cq = cq.where(User.nama.ilike(t))

    return await paginate(db, q, cq, page, per_page)


@router.get("/gaji/karyawan/{id}/slip")
async def cetak_slip_gaji(
    id: int, bulan: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko),
):
    """Generate slip gaji PDF untuk karyawan."""
    from fastapi.responses import StreamingResponse
    import io

    # Data karyawan
    r = await db.execute(select(User).where(User.id == id))
    karyawan = r.scalar_one_or_none()
    if not karyawan:
        raise HTTPException(404, "Karyawan tidak ditemukan")

    # Hitung komponen gaji dari bulan ini
    now = datetime.now()
    if bulan:
        dt = datetime.strptime(bulan, "%Y-%m")
        bln, thn = dt.month, dt.year
    else:
        bln, thn = now.month, now.year

    # Absensi bulan ini
    r_abs = await db.execute(
        select(
            func.count(Absensi.id).label("hadir"),
            func.sum(Absensi.status == "izin").label("izin"),
            func.sum(Absensi.status == "alpha").label("alpha"),
            func.sum(Absensi.status == "sakit").label("sakit"),
        ).where(
            Absensi.user_id == id,
            extract("month", Absensi.tgl) == bln,
            extract("year", Absensi.tgl) == thn,
        )
    )
    abs_data = r_abs.mappings().first()

    # Setting potongan
    r_set = await db.execute(select(StoreSetting).where(StoreSetting.cabang_id == cu.cabang_id))
    setting = r_set.scalar_one_or_none()

    potongan_izin = float(setting.nominal_potongan_izin or 0) * int(abs_data["izin"] or 0)
    potongan_alpha = float(setting.nominal_potongan_alfa or 0) * int(abs_data["alpha"] or 0)
    potongan_sakit = float(setting.nominal_potongan_sakit or 0) * int(abs_data["sakit"] or 0)
    total_potongan = potongan_izin + potongan_alpha + potongan_sakit

    # Overtime bulan ini
    r_ov = await db.execute(
        select(func.sum(Overtime.nominal).label("total_overtime")).where(
            Overtime.user_id == id, Overtime.status == "disetujui",
            extract("month", Overtime.tgl) == bln, extract("year", Overtime.tgl) == thn,
        )
    )
    total_overtime = float(r_ov.scalar_one_or_none() or 0)

    # Bonus
    r_bonus = await db.execute(
        select(func.sum(Bonus.nominal).label("total_bonus")).where(
            Bonus.user_id == id,
            extract("month", Bonus.created_at) == bln,
            extract("year", Bonus.created_at) == thn,
        )
    )
    total_bonus = float(r_bonus.scalar_one_or_none() or 0)

    gaji_bersih = float(karyawan.gaji_pokok or 0) + total_overtime + total_bonus - total_potongan

    try:
        from reportlab.lib.pagesizes import A5
        from reportlab.platypus import SimpleDocTemplate, Table, TableStyle, Paragraph, Spacer
        from reportlab.lib import colors
        from reportlab.lib.styles import getSampleStyleSheet

        buf = io.BytesIO()
        doc = SimpleDocTemplate(buf, pagesize=A5, leftMargin=24, rightMargin=24, topMargin=24, bottomMargin=24)
        styles = getSampleStyleSheet()
        story = []
        story.append(Paragraph("<b>SLIP GAJI</b>", styles["Title"]))
        story.append(Paragraph(f"Bulan: {bln:02d}/{thn}", styles["Normal"]))
        story.append(Spacer(1, 8))

        table_data = [
            ["Nama", karyawan.nama],
            ["Jabatan", karyawan.role],
            ["Gaji Pokok", f"Rp {float(karyawan.gaji_pokok or 0):,.0f}"],
            ["Overtime", f"Rp {total_overtime:,.0f}"],
            ["Bonus", f"Rp {total_bonus:,.0f}"],
            ["Potongan Izin", f"- Rp {potongan_izin:,.0f}"],
            ["Potongan Alpha", f"- Rp {potongan_alpha:,.0f}"],
            ["Potongan Sakit", f"- Rp {potongan_sakit:,.0f}"],
            ["GAJI BERSIH", f"Rp {gaji_bersih:,.0f}"],
        ]
        t = Table(table_data, colWidths=[130, 180])
        t.setStyle(TableStyle([
            ("FONTNAME", (0, -1), (-1, -1), "Helvetica-Bold"),
            ("BACKGROUND", (0, -1), (-1, -1), colors.lightblue),
            ("GRID", (0, 0), (-1, -1), 0.5, colors.grey),
            ("FONTSIZE", (0, 0), (-1, -1), 9),
        ]))
        story.append(t)
        doc.build(story)
        buf.seek(0)
        return StreamingResponse(buf, media_type="application/pdf",
                                  headers={"Content-Disposition": f"attachment; filename=slip_gaji_{karyawan.nama}_{bln}_{thn}.pdf"})
    except ImportError:
        return {"gaji_bersih": gaji_bersih, "total_potongan": total_potongan, "total_bonus": total_bonus}


# ─── Bonus ────────────────────────────────────────────────
@router.get("/gaji/bonus")
async def list_bonus(
    page: int = Query(1), per_page: int = Query(15),
    db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko),
):
    base = Bonus.cabang_id == cu.cabang_id
    q = select(Bonus.id, Bonus.user_id, Bonus.nominal, Bonus.keterangan, Bonus.created_at,
               User.nama.label("karyawan_nama")).outerjoin(User, User.id == Bonus.user_id).where(base).order_by(Bonus.created_at.desc())
    cq = select(func.count(Bonus.id)).where(base)
    return await paginate(db, q, cq, page, per_page)


@router.post("/gaji/bonus", status_code=201)
async def create_bonus(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    b = Bonus(user_id=body["user_id"], cabang_id=cu.cabang_id, nominal=body["nominal"], keterangan=body.get("keterangan"))
    db.add(b)
    await db.flush()
    return {"id": b.id}


@router.delete("/gaji/bonus/{id}")
async def delete_bonus(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    from sqlalchemy import delete as sd
    await db.execute(sd(Bonus).where(Bonus.id == id, Bonus.cabang_id == cu.cabang_id))
    return {"message": "Bonus dihapus"}


@router.post("/gaji/bonus/bulan-sebelumnya")
async def copy_bonus_bulan_sebelumnya(db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    """Copy bonus bulan lalu ke bulan ini — sama seperti di Laravel."""
    from datetime import timedelta
    now = datetime.now()
    last_month = (now.replace(day=1) - timedelta(days=1))
    bln, thn = last_month.month, last_month.year

    r = await db.execute(
        select(Bonus).where(
            Bonus.cabang_id == cu.cabang_id,
            extract("month", Bonus.created_at) == bln,
            extract("year", Bonus.created_at) == thn,
        )
    )
    bonuses = r.scalars().all()
    count = 0
    for b in bonuses:
        db.add(Bonus(user_id=b.user_id, cabang_id=cu.cabang_id, nominal=b.nominal, keterangan=b.keterangan))
        count += 1
    return {"message": f"{count} bonus berhasil disalin dari bulan sebelumnya"}
