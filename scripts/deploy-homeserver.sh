#!/usr/bin/env bash

set -euo pipefail

log() {
    printf '[%s] %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*"
}

run_step() {
    local label="$1"
    shift
    local started_at=$SECONDS
    log "START: ${label}"
    "$@"
    log "DONE: ${label} (${SECONDS-started_at}s)"
}

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

resolve_sqlite_db_path() {
    local dbConnection dbDatabase
    dbConnection="$(grep -E '^DB_CONNECTION=' .env | tail -n 1 | cut -d= -f2- | tr -d '\r' || true)"
    dbDatabase="$(grep -E '^DB_DATABASE=' .env | tail -n 1 | cut -d= -f2- | tr -d '\r' || true)"

    if [[ "${dbConnection}" != "sqlite" ]]; then
        return 1
    fi

    if [[ -z "${dbDatabase}" ]]; then
        printf '%s\n' "/var/www/html/database/database.sqlite"
        return 0
    fi

    if [[ "${dbDatabase}" == /* ]]; then
        printf '%s\n' "${dbDatabase}"
        return 0
    fi

    printf '%s\n' "/var/www/html/${dbDatabase}"
}

run_step "Configure git safe.directory" git config --global --add safe.directory "${APP_DIR}"
run_step "Fetch branch ${BRANCH}" git fetch origin "${BRANCH}"
run_step "Checkout branch ${BRANCH}" git checkout "${BRANCH}"
run_step "Pull latest branch ${BRANCH}" git pull --ff-only origin "${BRANCH}"

if [[ ! -f .env ]]; then
    cp .env.example .env
fi

LOCAL_UID="$(id -u)"
LOCAL_GID="$(id -g)"
EFFECTIVE_UID="${LOCAL_UID}"
EFFECTIVE_GID="${LOCAL_GID}"

if [[ "${LOCAL_UID}" == "0" || "${LOCAL_GID}" == "0" ]]; then
    EFFECTIVE_UID="33"
    EFFECTIVE_GID="33"
    echo "Deploy session is running as root. Using safe app UID/GID ${EFFECTIVE_UID}:${EFFECTIVE_GID}."
fi

APP_IMAGE="${COMPOSE_PROJECT_NAME:-monitoring-ppa}-app:latest"
IMAGE_UID="$(docker run --rm "${APP_IMAGE}" id -u www-data 2>/dev/null || true)"
IMAGE_GID="$(docker run --rm "${APP_IMAGE}" id -g www-data 2>/dev/null || true)"
IMAGE_UID_GID_MISMATCH=0

if [[ -n "${IMAGE_UID}" && -n "${IMAGE_GID}" ]] && [[ "${IMAGE_UID}" != "${EFFECTIVE_UID}" || "${IMAGE_GID}" != "${EFFECTIVE_GID}" ]]; then
    IMAGE_UID_GID_MISMATCH=1
fi

if [[ "${FORCE_REBUILD}" == "1" || "${FORCE_REBUILD}" == "true" || "${IMAGE_UID_GID_MISMATCH}" == "1" ]]; then
    if [[ "${IMAGE_UID_GID_MISMATCH}" == "1" ]]; then
        echo "Detected app image UID/GID mismatch (image: ${IMAGE_UID}:${IMAGE_GID}, target: ${EFFECTIVE_UID}:${EFFECTIVE_GID}). Rebuilding app image..."
    fi
    HOST_UID="${EFFECTIVE_UID}" HOST_GID="${EFFECTIVE_GID}" run_step "Build app image" compose build app
elif ! docker image inspect "${APP_IMAGE}" > /dev/null 2>&1; then
    echo "Image ${APP_IMAGE} not found. Building app image first..."
    HOST_UID="${EFFECTIVE_UID}" HOST_GID="${EFFECTIVE_GID}" run_step "Build app image (missing image)" compose build app
fi

HOST_UID="${EFFECTIVE_UID}" HOST_GID="${EFFECTIVE_GID}" run_step "Start containers" compose up -d --no-build

log "START: Read app www-data UID/GID"
uid_gid_started_at=$SECONDS
APP_UID="$(compose exec -T app id -u www-data | tr -d '\r')"
APP_GID="$(compose exec -T app id -g www-data | tr -d '\r')"
log "DONE: Read app www-data UID/GID (${SECONDS-uid_gid_started_at}s)"
SQLITE_DB_PATH="$(resolve_sqlite_db_path || true)"
SQLITE_DB_DIR=""

if [[ -z "${APP_UID}" || -z "${APP_GID}" ]]; then
    echo "Failed to resolve www-data UID/GID from app container." >&2
    exit 1
fi

if [[ -n "${SQLITE_DB_PATH}" ]]; then
    SQLITE_DB_DIR="$(dirname "${SQLITE_DB_PATH}")"
fi

run_step "Fix storage/bootstrap permissions" compose exec -T --user root app sh -lc "
set -eu
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache
touch /var/www/html/storage/logs/laravel.log
chown -R ${APP_UID}:${APP_GID} /var/www/html/storage /var/www/html/bootstrap/cache
find /var/www/html/storage /var/www/html/bootstrap/cache -type d -exec chmod 775 {} \\;
find /var/www/html/storage /var/www/html/bootstrap/cache -type f -exec chmod 664 {} \\;
"

if [[ -n "${SQLITE_DB_PATH}" && -n "${SQLITE_DB_DIR}" ]]; then
    run_step "Fix sqlite directory permissions" compose exec -T --user root app sh -lc "
set -eu
mkdir -p '${SQLITE_DB_DIR}'
touch '${SQLITE_DB_PATH}'
chown -R ${APP_UID}:${APP_GID} '${SQLITE_DB_DIR}'
find '${SQLITE_DB_DIR}' -type d -exec chmod 775 {} \\;
find '${SQLITE_DB_DIR}' -type f -exec chmod 664 {} \\;
"
fi

run_step "Configure git safe.directory in app container" compose exec -T --user www-data -e HOME=/tmp app git config --global --add safe.directory /var/www/html || true
run_step "Composer install (production)" compose exec -T --user www-data -e HOME=/tmp app composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
if ! grep -qE '^APP_KEY=base64:' .env; then
    run_step "Generate APP_KEY" compose exec -T --user www-data -e HOME=/tmp app php artisan key:generate --force
fi
run_step "Run migrations" compose exec -T --user www-data -e HOME=/tmp app php artisan migrate --force
run_step "Clear optimize cache" compose exec -T --user www-data -e HOME=/tmp app php artisan optimize:clear
run_step "Clear filament optimize cache" compose exec -T --user www-data -e HOME=/tmp app php artisan filament:optimize-clear
run_step "Optimize Laravel" compose exec -T --user www-data -e HOME=/tmp app php artisan optimize
run_step "Optimize Filament" compose exec -T --user www-data -e HOME=/tmp app php artisan filament:optimize
run_step "Restart queue workers" compose exec -T --user www-data -e HOME=/tmp app php artisan queue:restart
