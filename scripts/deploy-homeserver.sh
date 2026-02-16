#!/usr/bin/env bash

set -euo pipefail

APP_DIR="${APP_DIR:-/opt/monitoring-ppa}"
BRANCH="${BRANCH:-main}"

if [[ ! -d "${APP_DIR}" ]]; then
    echo "APP_DIR does not exist: ${APP_DIR}" >&2
    exit 1
fi

cd "${APP_DIR}"

git config --global --add safe.directory "${APP_DIR}"
git fetch origin "${BRANCH}"
git checkout "${BRANCH}"
git pull --ff-only origin "${BRANCH}"

if [[ ! -f .env ]]; then
    cp .env.example .env
fi

export UID
UID="$(id -u)"
export GID
GID="$(id -g)"

docker compose up -d --build
docker compose exec -T app composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
if ! grep -qE '^APP_KEY=base64:' .env; then
    docker compose exec -T app php artisan key:generate --force
fi
docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan optimize
