"""
Target API — Target Servis (Teknisi), Target Sales, copy bulan sebelumnya.
Multi-cabang: semua difilter per cabang_id.
"""
from fastapi import APIRouter, Depends, Query, HTTPException
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, func, and_, update, extract
from typing import Optional
from datetime import datetime, timedelta

from app.core.database import get_db
from app.core.security import require_kepala_toko, require_any
from app.models.models import User, TeknisiTarget, SalesTarget
from app.utils.query import paginate

router = APIRouter()


# ─── Target Teknisi (Servis) ──────────────────────────────
@router.get("/target")
async def list_target(
    page: int = Query(1), per_page: int = Query(15),
    bulan: Optional[str] = Query(None),
    user_id: Optional[int] = Query(None),
    db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko),
):
    base = and_(TeknisiTarget.cabang_id == cu.cabang_id)
    q = select(
        TeknisiTarget.id, TeknisiTarget.user_id, TeknisiTarget.tipe,
        TeknisiTarget.item, TeknisiTarget.nominal, TeknisiTarget.bonus_nominal,
        TeknisiTarget.created_at, User.nama.label("teknisi_nama"),
    ).outerjoin(User, User.id == TeknisiTarget.user_id).where(base).order_by(TeknisiTarget.created_at.desc())
    cq = select(func.count(TeknisiTarget.id)).where(base)

    if bulan:
        try:
            dt = datetime.strptime(bulan, "%Y-%m")
            q = q.where(extract("year", TeknisiTarget.created_at) == dt.year, extract("month", TeknisiTarget.created_at) == dt.month)
            cq = cq.where(extract("year", TeknisiTarget.created_at) == dt.year, extract("month", TeknisiTarget.created_at) == dt.month)
        except ValueError:
            pass
    if user_id:
        q = q.where(TeknisiTarget.user_id == user_id)
        cq = cq.where(TeknisiTarget.user_id == user_id)

    return await paginate(db, q, cq, page, per_page)


@router.post("/target", status_code=201)
async def create_target(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    t = TeknisiTarget(
        user_id=body["user_id"],
        cabang_id=cu.cabang_id,
        tipe=body.get("tipe", "item"),       # item | nominal
        item=body.get("item"),               # target jumlah item
        nominal=body.get("nominal"),         # target nominal (profit)
        bonus_nominal=body.get("bonus_nominal", 0),
    )
    db.add(t)
    await db.flush()
    return {"id": t.id}


@router.put("/target/{id}")
async def update_target(id: int, body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    await db.execute(
        update(TeknisiTarget).where(TeknisiTarget.id == id, TeknisiTarget.cabang_id == cu.cabang_id)
        .values(tipe=body.get("tipe"), item=body.get("item"), nominal=body.get("nominal"),
                bonus_nominal=body.get("bonus_nominal"), updated_at=datetime.utcnow())
    )
    return {"message": "Target diperbarui"}


@router.delete("/target/{id}")
async def delete_target(id: int, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    from sqlalchemy import delete as sd
    await db.execute(sd(TeknisiTarget).where(TeknisiTarget.id == id, TeknisiTarget.cabang_id == cu.cabang_id))
    return {"message": "Target dihapus"}


@router.post("/target/delete-batch")
async def delete_target_batch(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    from sqlalchemy import delete as sd
    await db.execute(sd(TeknisiTarget).where(TeknisiTarget.id.in_(body.get("ids", [])), TeknisiTarget.cabang_id == cu.cabang_id))
    return {"message": "Target batch dihapus"}


@router.post("/target/bulan-sebelumnya")
async def copy_target_bulan_sebelumnya(db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    """Copy target bulan lalu ke bulan ini — sama logika Laravel."""
    now = datetime.now()
    last_month = (now.replace(day=1) - timedelta(days=1))
    bln, thn = last_month.month, last_month.year

    r = await db.execute(
        select(TeknisiTarget).where(
            TeknisiTarget.cabang_id == cu.cabang_id,
            extract("month", TeknisiTarget.created_at) == bln,
            extract("year", TeknisiTarget.created_at) == thn,
        )
    )
    targets = r.scalars().all()
    count = 0
    for t in targets:
        db.add(TeknisiTarget(
            user_id=t.user_id, cabang_id=cu.cabang_id,
            tipe=t.tipe, item=t.item, nominal=t.nominal, bonus_nominal=t.bonus_nominal,
        ))
        count += 1
    return {"message": f"{count} target berhasil disalin dari bulan sebelumnya"}


# ─── Target Sales ──────────────────────────────────────────
@router.get("/target-sales")
async def list_target_sales(
    page: int = Query(1), per_page: int = Query(15),
    bulan: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko),
):
    base = SalesTarget.cabang_id == cu.cabang_id
    q = select(
        SalesTarget.id, SalesTarget.user_id, SalesTarget.tipe,
        SalesTarget.item, SalesTarget.nominal, SalesTarget.bonus_nominal,
        SalesTarget.created_at, User.nama.label("sales_nama"),
    ).outerjoin(User, User.id == SalesTarget.user_id).where(base).order_by(SalesTarget.created_at.desc())
    cq = select(func.count(SalesTarget.id)).where(base)

    if bulan:
        try:
            dt = datetime.strptime(bulan, "%Y-%m")
            q = q.where(extract("year", SalesTarget.created_at) == dt.year, extract("month", SalesTarget.created_at) == dt.month)
            cq = cq.where(extract("year", SalesTarget.created_at) == dt.year, extract("month", SalesTarget.created_at) == dt.month)
        except ValueError:
            pass

    return await paginate(db, q, cq, page, per_page)


@router.post("/target-sales", status_code=201)
async def create_target_sales(body: dict, db: AsyncSession = Depends(get_db), cu=Depends(require_kepala_toko)):
    t = SalesTarget(
        user_id=body["user_id"], cabang_id=cu.cabang_id,
        tipe=body.get("tipe", "nominal"),
        item=body.get("item"), nominal=body.get("nominal"),
        bonus_nominal=body.get("bonus_nominal", 0),
    )
    db.add(t)
    await db.flush()
    return {"id": t.id}


# ─── Target Teknisi Detail (progress bulan ini) ──────────
@router.get("/target-teknisi")
async def target_teknisi_progress(
    bulan: Optional[str] = Query(None),
    db: AsyncSession = Depends(get_db),
    cu=Depends(require_any),
):
    """
    Progress target teknisi bulan ini — sama dengan getTargetTeknisiStats di Laravel.
    Digunakan di dashboard teknisi.
    """
    from app.models.models import TransaksiServis

    now = datetime.now()
    if bulan:
        dt = datetime.strptime(bulan, "%Y-%m")
        bln, thn = dt.month, dt.year
    else:
        bln, thn = now.month, now.year

    # Untuk teknisi — hanya lihat target sendiri
    user_id = cu.id if cu.is_teknisi else None

    # Ambil teknisi di cabang
    filters_u = [User.cabang_id == cu.cabang_id, User.role == "Teknisi", User.deleted_at.is_(None)]
    if user_id:
        filters_u.append(User.id == user_id)
    r_users = await db.execute(select(User.id, User.nama).where(and_(*filters_u)))
    users = r_users.mappings().all()

    result = []
    for u in users:
        # Target bulan ini
        r_t = await db.execute(
            select(TeknisiTarget).where(
                TeknisiTarget.user_id == u["id"],
                TeknisiTarget.cabang_id == cu.cabang_id,
                extract("month", TeknisiTarget.created_at) == bln,
                extract("year", TeknisiTarget.created_at) == thn,
            )
        )
        targets = r_t.scalars().all()

        if not targets:
            result.append({"user_id": u["id"], "nama": u["nama"], "target_text": "-", "progres": 0, "reward": 0})
            continue

        tipe = targets[0].tipe
        bonus_nominal = sum(float(t.bonus_nominal or 0) for t in targets)

        # Pencapaian
        r_s = await db.execute(
            select(
                func.count(TransaksiServis.id).label("jumlah"),
                func.sum(TransaksiServis.profit).label("profit"),
            ).where(
                TransaksiServis.teknisi_id == u["id"],
                TransaksiServis.cabang_id == cu.cabang_id,
                TransaksiServis.status == "sudah_diambil",
                extract("month", TransaksiServis.tgl_selesai) == bln,
                extract("year", TransaksiServis.tgl_selesai) == thn,
            )
        )
        pencapaian = r_s.mappings().first()

        if tipe == "nominal":
            achieved = float(pencapaian["profit"] or 0)
            target_val = sum(float(t.nominal or 0) for t in targets)
            target_text = f"Rp {target_val:,.0f}"
            achieved_text = f"Rp {achieved:,.0f}"
        else:
            achieved = pencapaian["jumlah"] or 0
            target_val = sum(float(t.item or 0) for t in targets)
            target_text = f"{target_val:.0f} Item"
            achieved_text = f"{achieved} Item"

        progres = min(100, (achieved / target_val * 100) if target_val > 0 else 0)
        reward = bonus_nominal if progres >= 100 else 0

        result.append({
            "user_id": u["id"], "nama": u["nama"],
            "target_text": target_text,
            "achieved_text": achieved_text,
            "progres": round(progres, 1),
            "reward": reward,
        })

    return result
