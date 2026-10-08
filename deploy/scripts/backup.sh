#!/usr/bin/env bash
# ==============================================================================
# CloudCampus Storage — Skrip Pencadangan Otomatis (Database & Berkas Pengguna)
# ==============================================================================

set -euo pipefail

APP_DIR="/var/www/cloudcampus"
BACKUP_DIR="/var/backups/cloudcampus"
DATE=$(date +"%Y%m%d_%H%M%S")
RETENTION_DAYS=14

mkdir -p "${BACKUP_DIR}/db"
mkdir -p "${BACKUP_DIR}/storage"

echo "[$(date)] Memulai pencadangan CloudCampus Storage..."

# 1. Backup Database
if [ -f "${APP_DIR}/database/database.sqlite" ]; then
    echo "Mencadangkan database SQLite..."
    sqlite3 "${APP_DIR}/database/database.sqlite" ".backup '${BACKUP_DIR}/db/db_${DATE}.sqlite'"
    gzip "${BACKUP_DIR}/db/db_${DATE}.sqlite"
else
    # Jika menggunakan MySQL / MariaDB (ekstrak kredensial dari .env)
    DB_USER=$(grep '^DB_USERNAME=' "${APP_DIR}/.env" | cut -d '=' -f2 || echo "root")
    DB_PASS=$(grep '^DB_PASSWORD=' "${APP_DIR}/.env" | cut -d '=' -f2 || echo "")
    DB_NAME=$(grep '^DB_DATABASE=' "${APP_DIR}/.env" | cut -d '=' -f2 || echo "cloudcampus")
    
    echo "Mencadangkan database MySQL (${DB_NAME})..."
    mysqldump -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" | gzip > "${BACKUP_DIR}/db/mysql_${DATE}.sql.gz"
fi

# 2. Backup Direktori Berkas Pengguna (storage/app/users)
if [ -d "${APP_DIR}/storage/app/users" ]; then
    echo "Mencadangkan berkas penyimpanan pengguna..."
    tar -czf "${BACKUP_DIR}/storage/users_storage_${DATE}.tar.gz" -C "${APP_DIR}/storage/app" users
fi

# 3. Rotasi & Pembersihan Cadangan Lawas (> RETENTION_DAYS)
echo "Membersihkan cadangan yang lebih lama dari ${RETENTION_DAYS} hari..."
find "${BACKUP_DIR}/db" -type f -mtime +"${RETENTION_DAYS}" -delete
find "${BACKUP_DIR}/storage" -type f -mtime +"${RETENTION_DAYS}" -delete

echo "[$(date)] Pencadangan selesai dengan sukses. Arsip tersimpan di ${BACKUP_DIR}"
