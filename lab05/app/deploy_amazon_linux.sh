#!/usr/bin/env bash
set -euo pipefail

# Simple deploy script for Amazon Linux (AL2/AL2023) with Apache + PHP.
# Copies the current directory into /var/www/html/app and configures env vars.

# IMPORTANT: quote actual RDS endpoints; hyphens confuse shell if unquoted.
# Override via env vars when running if needed.
DB_MASTER_HOST="${DB_MASTER_HOST:-project-rds-mysql-prod.cpyogq48kfp9.eu-central-1.rds.amazonaws.com}"
DB_REPLICA_HOST="${DB_REPLICA_HOST:-project-rds-mysql-read-replica.cpyogq48kfp9.eu-central-1.rds.amazonaws.com}"
DB_NAME="${DB_NAME:-project_db}"
DB_USER="${DB_USER:-admin}"
DB_PASS="${DB_PASS:-}"
DB_PORT="${DB_PORT:-3306}"

APP_SRC="$(cd "$(dirname "$0")" && pwd)"
APP_DST="/var/www/html/app"

echo "[1/4] Installing Apache + PHP ..."
if command -v dnf >/dev/null 2>&1; then
  sudo dnf install -y httpd php php-mysqlnd php-pdo
else
  sudo yum install -y httpd php php-mysqlnd php-pdo
fi

echo "[2/4] Deploying application to ${APP_DST} ..."
sudo mkdir -p "$APP_DST"
sudo rsync -av --delete "$APP_SRC"/ "$APP_DST"/
sudo chown -R apache:apache "$APP_DST"

echo "[3/4] Writing Apache env config ..."
sudo tee /etc/httpd/conf.d/rds-app-env.conf >/dev/null <<EOF
SetEnv DB_MASTER_HOST ${DB_MASTER_HOST}
SetEnv DB_REPLICA_HOST ${DB_REPLICA_HOST}
SetEnv DB_NAME ${DB_NAME}
SetEnv DB_USER ${DB_USER}
SetEnv DB_PASS ${DB_PASS}
SetEnv DB_PORT ${DB_PORT}
EOF

echo "[4/4] Enabling and starting httpd ..."
sudo systemctl enable httpd
sudo systemctl restart httpd

echo "Deployment complete. Open http://<EC2-Public-IP>/app/ to use the app."
