"""
Pydantic schemas untuk Auth
"""
from pydantic import BaseModel
from typing import Optional


class LoginRequest(BaseModel):
    username: str
    password: str


class UserMe(BaseModel):
    id: int
    nama: Optional[str] = None
    username: str
    role: str
    cabang_id: Optional[int] = None
    foto: Optional[str] = None
    assigned_cabang_ids: Optional[list[int]] = None
    assigned_cabangs: Optional[list[dict]] = None

    class Config:
        from_attributes = True



class TokenResponse(BaseModel):
    access_token: str
    refresh_token: str
    token_type: str = "bearer"
    user: UserMe
