#!/usr/bin/env bash
# ==============================================================================
# CloudCampus Storage — Pemasangan SSL Let's Encrypt Otomatis via Certbot
# ==============================================================================

set -euo pipefail

DOMAIN="${1:-cloud.kampus.ac.id}"
EMAIL="${2:-admin@kampus.ac.id}"

echo "Memasang Certbot dan Nginx plugin..."
apt-get update
apt-get install -y certbot python3-certbot-nginx

echo "Mengambil sertifikat SSL gratis dari Let's Encrypt untuk ${DOMAIN}..."
certbot --nginx -d "${DOMAIN}" --non-interactive --agree-tos -m "${EMAIL}" --redirect

echo "Menguji pembaruan sertifikat otomatis (dry-run)..."
certbot renew --dry-run

echo "SSL berhasil dipasang dan konfigurasi HTTPS di Nginx telah aktif!"
