#!/bin/sh
set -eu

mkdir -p storage/logs
touch storage/logs/app.log

# Render Persistent Disk: mount path /data kullanılıyorsa uploads kalıcı olur
if [ -d /data ]; then
  mkdir -p /data/uploads
  rm -rf public/uploads 2>/dev/null || true
  ln -sfn /data/uploads public/uploads
else
  mkdir -p public/uploads
fi

PORT="${PORT:-10000}"

exec php -S "0.0.0.0:${PORT}" -t public public/index.php
