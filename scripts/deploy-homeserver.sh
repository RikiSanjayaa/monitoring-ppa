#!/usr/bin/env bash

set -euo pipefail

APP_DIR="${APP_DIR:-/opt/monitoring-ppa}"
BRANCH="${BRANCH:-main}"
FORCE_REBUILD="${FORCE_REBUILD:-0}"
COMPOSE_FILE_PATH="${COMPOSE_FILE_PATH:-compose.prod.yaml}"

if [[ ! -d "${APP_DIR}" ]]; then
    echo "APP_DIR does not exist: ${APP_DIR}" >&2
    exit 1
fi

cd "${APP_DIR}"

if [[ ! -f "${COMPOSE_FILE_PATH}" ]]; then
    echo "Compose file not found: ${COMPOSE_FILE_PATH}" >&2
    exit 1
fi

compose() {
    docker compose -f "${COMPOSE_FILE_PATH}" "$@"
}

git config --global --add safe.directory "${APP_DIR}"
git fetch origin "${BRANCH}"
git checkout "${BRANCH}"
git pull --ff-only origin "${BRANCH}"

if [[ ! -f .env ]]; then
    cp .env.example .env
fi

LOCAL_UID="$(id -u)"
LOCAL_GID="$(id -g)"

if [[ "${FORCE_REBUILD}" == "1" || "${FORCE_REBUILD}" == "true" ]]; then
    HOST_UID="${LOCAL_UID}" HOST_GID="${LOCAL_GID}" compose build app
fi

HOST_UID="${LOCAL_UID}" HOST_GID="${LOCAL_GID}" compose up -d --no-build

compose exec -T --user www-data -e HOME=/tmp app git config --global --add safe.directory /var/www/html || true
compose exec -T --user www-data -e HOME=/tmp app composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
if ! grep -qE '^APP_KEY=base64:' .env; then
    compose exec -T --user www-data -e HOME=/tmp app php artisan key:generate --force
fi
compose exec -T --user www-data -e HOME=/tmp app php artisan migrate --force
compose exec -T --user www-data -e HOME=/tmp app php artisan optimize
compose exec -T --user www-data -e HOME=/tmp app php artisan filament:optimize
compose exec -T --user www-data -e HOME=/tmp app php artisan queue:restart
