#!/usr/bin/env bash
# Chạy trên VPS: ./deploy.sh  (kéo code mới từ GitHub và áp dụng)
set -euo pipefail

cd "$(dirname "$0")"

# Bật bảo trì trong lúc cập nhật, luôn tắt lại kể cả khi lỗi giữa chừng
php artisan down --retry=10 || true
trap 'php artisan up' EXIT

git pull --ff-only
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan optimize:clear
php artisan optimize

echo "Deploy xong: $(git log -1 --format='%h %s')"
