"""
Enterprise-grade query utilities:
- Server-side pagination
- Search filter builder
- Aggregate helpers
- Soft-delete filter
"""
from typing import TypeVar, Generic, Optional, List, Any, Type
from sqlalchemy import select, func, and_, or_, desc, asc
from sqlalchemy.ext.asyncio import AsyncSession
from pydantic import BaseModel

ModelT = TypeVar("ModelT")


class PaginatedResponse(BaseModel, Generic[ModelT]):
    data: List[Any]
    total: int
    page: int
    per_page: int
    last_page: int
    from_: Optional[int] = None
    to_: Optional[int] = None

    class Config:
        populate_by_name = True


async def paginate(
    db: AsyncSession,
    query,
    count_query,
    page: int = 1,
    per_page: int = 15,
) -> dict:
    """
    Efficient pagination — pisah count query dari data query.
    Count query di-cache terpisah karena lebih berat.
    """
    per_page = min(per_page, 100)  # max 100 per page
    offset = (page - 1) * per_page

    # Execute count dan data secara bersamaan (async)
    total_result = await db.execute(count_query)
    total = total_result.scalar() or 0

    data_result = await db.execute(query.offset(offset).limit(per_page))

    return {
        "data": data_result.mappings().all() if hasattr(data_result, 'mappings') else data_result.all(),
        "total": total,
        "page": page,
        "per_page": per_page,
        "last_page": max(1, (total + per_page - 1) // per_page),
        "from_": offset + 1 if total > 0 else None,
        "to_": min(offset + per_page, total) if total > 0 else None,
    }


def apply_search(query, columns: list, search: str):
    """Full-text search di multiple columns."""
    if not search or not search.strip():
        return query
    term = f"%{search.strip()}%"
    conditions = [col.ilike(term) for col in columns]
    return query.where(or_(*conditions))


def apply_date_range(query, column, date_from=None, date_to=None):
    """Filter tanggal range."""
    if date_from:
        query = query.where(column >= date_from)
    if date_to:
        query = query.where(column <= date_to)
    return query


def soft_delete_filter(column):
    """Filter soft delete."""
    return column.is_(None)
