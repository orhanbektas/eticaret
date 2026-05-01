#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DEPLOY_PATH="${DEPLOY_PATH:-$HOME/public_html}"

echo "[deploy] Repo: ${REPO_ROOT}"
echo "[deploy] Target: ${DEPLOY_PATH}"

if [[ ! -d "${DEPLOY_PATH}" ]]; then
  mkdir -p "${DEPLOY_PATH}"
fi

if command -v rsync >/dev/null 2>&1; then
  rsync -a --delete \
    --exclude '.git/' \
    --exclude '.cpanel.yml' \
    --exclude 'scripts/' \
    --exclude 'logs/' \
    "${REPO_ROOT}/" "${DEPLOY_PATH}/"
else
  tar --exclude='.git' --exclude='.cpanel.yml' --exclude='scripts' --exclude='logs' -C "${REPO_ROOT}" -cf - . | tar -C "${DEPLOY_PATH}" -xf -
fi

echo "[deploy] Completed."
