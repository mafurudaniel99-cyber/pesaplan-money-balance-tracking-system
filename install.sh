#!/usr/bin/env bash
# PesaPlan — Quick install script (Linux / macOS)
#
# Usage:
#   1. Edit config/database.php with your MySQL credentials.
#   2. Run: ./install.sh
#
# This script:
#   - Creates the pesaplan database
#   - Imports schema.sql and seed.sql
#   - Verifies the install by counting tables

set -e

DB_HOST="127.0.0.1"
DB_NAME="pesaplan"

# Read credentials from config/database.php
DB_USER=$(grep -E "'user'" config/database.php | sed -E "s/.*'user'\s*=>\s*'([^']*)'.*/\1/")
DB_PASS=$(grep -E "'pass'" config/database.php | sed -E "s/.*'pass'\s*=>\s*'([^']*)'.*/\1/")

echo "=========================================="
echo "  PesaPlan Installer"
echo "=========================================="
echo "Host:     $DB_HOST"
echo "Database: $DB_NAME"
echo "User:     $DB_USER"
echo ""

read -p "Continue? [y/N] " confirm
if [[ "$confirm" != "y" && "$confirm" != "Y" ]]; then
    echo "Aborted."
    exit 1
fi

echo ""
echo "→ Creating database..."
mysql -h "$DB_HOST" -u "$DB_USER" ${DB_PASS:+-p"$DB_PASS"} -e \
    "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "→ Importing schema..."
mysql -h "$DB_HOST" -u "$DB_USER" ${DB_PASS:+-p"$DB_PASS"} "$DB_NAME" < database/schema.sql

echo "→ Importing seed data..."
mysql -h "$DB_HOST" -u "$DB_USER" ${DB_PASS:+-p"$DB_PASS"} "$DB_NAME" < database/seed.sql

echo ""
echo "→ Verifying..."
TABLE_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" ${DB_PASS:+-p"$DB_PASS"} "$DB_NAME" -sN -e \
    "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$DB_NAME';")
echo "Tables created: $TABLE_COUNT (expected: 18)"

echo ""
echo "=========================================="
echo "  Installation complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "  1. Start Apache (XAMPP/LAMP) and point DocumentRoot to: $(pwd)/public"
echo "  2. Open the application in your browser."
echo "  3. Register your first account and complete the onboarding wizard."
echo ""
echo "See README.md and docs/INSTALLATION.md for details."
