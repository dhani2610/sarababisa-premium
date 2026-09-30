from pydantic_settings import BaseSettings
from typing import Optional


class Settings(BaseSettings):
    # App
    APP_NAME: str = "SarabaBisa"
    APP_ENV: str = "development"
    APP_DEBUG: bool = True
    APP_URL: str = "http://localhost:8000"

    # Database
    DB_HOST: str = "127.0.0.1"
    DB_PORT: int = 3306
    DB_NAME: str = "pj"
    DB_USER: str = "root"
    DB_PASSWORD: str = ""

    # JWT
    JWT_SECRET: str = "sarababisa-super-secret-key"
    JWT_ALGORITHM: str = "HS256"
    JWT_ACCESS_TOKEN_EXPIRE_MINUTES: int = 1440
    JWT_REFRESH_TOKEN_EXPIRE_DAYS: int = 30

    # Redis
    REDIS_URL: str = "redis://localhost:6379/0"

    # CORS
    FRONTEND_URL: str = "http://localhost:3000"

    # Mail
    MAIL_HOST: str = "smtp.gmail.com"
    MAIL_PORT: int = 587
    MAIL_USERNAME: str = ""
    MAIL_PASSWORD: str = ""
    MAIL_FROM: str = ""
    MAIL_FROM_NAME: str = "SarabaBisa"

    # Midtrans
    MIDTRANS_MERCHANT_ID: str = ""
    MIDTRANS_CLIENT_KEY: str = ""
    MIDTRANS_SERVER_KEY: str = ""
    MIDTRANS_IS_PRODUCTION: bool = False

    @property
    def DATABASE_URL(self) -> str:
        return f"mysql+pymysql://{self.DB_USER}:{self.DB_PASSWORD}@{self.DB_HOST}:{self.DB_PORT}/{self.DB_NAME}?charset=utf8mb4"

    class Config:
        env_file = ".env"
        case_sensitive = True


settings = Settings()
