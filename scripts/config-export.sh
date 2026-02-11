#!/bin/bash
# ===========================================
# IBEW Drupal 11 - Config Export Script
# ===========================================
# Exports Drupal config to config/sync for version control.
# Run this after making config changes in the admin UI.
# ===========================================

set -e

echo "============================================"
echo " IBEW Drupal 11 - Config Export"
echo "============================================"
echo ""

if [ "$IS_DDEV_PROJECT" = "true" ] || command -v ddev &>/dev/null; then
    echo "→ Exporting config via DDEV..."
    ddev drush config:export -y
else
    echo "→ Exporting config via Drush..."
    vendor/bin/drush config:export -y
fi

echo ""
echo "✓ Config exported to config/sync/"
echo ""
echo "Next steps:"
echo "  1. Review changes: git diff config/"
echo "  2. Commit: git add config/ && git commit -m 'Update config'"
echo "  3. Push: git push origin main"
echo "  (GitHub Actions will auto-deploy and import config on the server)"
echo ""
