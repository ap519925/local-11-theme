#!/bin/bash
# ===========================================
# IBEW Drupal 11 - Database Sync Script
# ===========================================
# Usage: Run from the project root.
#
# Export from DDEV (local → file):
#   ./scripts/db-export.sh
#
# Import to DDEV (file → local):
#   ./scripts/db-import.sh
#
# For production, use these via SSH manually.
# ===========================================

set -e

TIMESTAMP=$(date +%Y%m%d_%H%M%S)
EXPORT_DIR="backups"
EXPORT_FILE="${EXPORT_DIR}/db_${TIMESTAMP}.sql.gz"

mkdir -p "$EXPORT_DIR"

echo "============================================"
echo " IBEW Drupal 11 - Database Export"
echo "============================================"
echo ""

# Detect environment
if [ "$IS_DDEV_PROJECT" = "true" ] || command -v ddev &>/dev/null; then
    echo "→ Detected DDEV environment"
    echo "→ Exporting database..."
    ddev export-db --gzip --file="$EXPORT_FILE"
else
    echo "→ Detected production environment"
    echo "→ Exporting database via Drush..."
    vendor/bin/drush sql:dump --gzip --result-file="$EXPORT_FILE"
fi

echo ""
echo "✓ Database exported to: $EXPORT_FILE"
echo "  Size: $(du -h "$EXPORT_FILE" | cut -f1)"
echo ""
echo "To import this on the server:"
echo "  gunzip < $EXPORT_FILE | drush sql:cli"
echo ""
