#!/bin/bash

# Quick Deployment Script for Template Variables Fix
# This script should be run on the remote server to pull the latest fixes

echo "=================================="
echo "OnLive WhatsApp Order - Deploy Script"
echo "=================================="
echo ""

PLUGIN_DIR="/home/user/public_html/wp-content/plugins/onlive-whatsapp-order"

if [ ! -d "$PLUGIN_DIR" ]; then
    echo "ERROR: Plugin directory not found at $PLUGIN_DIR"
    echo "Please update the PLUGIN_DIR variable in this script"
    exit 1
fi

echo "[1/4] Stopping any background processes..."
cd "$PLUGIN_DIR"

echo "[2/4] Pulling latest code from GitHub..."
git fetch origin
git pull origin main

if [ $? -ne 0 ]; then
    echo "ERROR: Git pull failed. Check your internet connection and GitHub credentials."
    exit 1
fi

echo "[3/4] Setting proper file permissions..."
find "$PLUGIN_DIR" -type f -name "*.php" -exec chmod 644 {} \;
find "$PLUGIN_DIR" -type d -exec chmod 755 {} \;

echo "[4/4] Clearing WordPress cache..."
# Clear WordPress transients (in-memory cache)
wp transient delete-all

# If using LiteSpeed Cache
if [ -d "$PLUGIN_DIR/../../../wp-content/plugins/litespeed-cache" ]; then
    wp lscache-purge all
fi

echo ""
echo "=================================="
echo "✅ Deployment Complete!"
echo "=================================="
echo ""
echo "Version Updated:"
git log -1 --oneline
echo ""
echo "Next Steps:"
echo "1. Clear your browser cache (Ctrl+Shift+Del)"
echo "2. Visit your site and check WhatsApp button"
echo "3. Test template variables in the message"
echo ""
echo "If still seeing empty values:"
echo "1. Run: wp cache flush (if using WP Super Cache)"
echo "2. Run: rm -rf /tmp/php* (clear PHP cache)"
echo "3. Restart PHP: sudo systemctl restart php-fpm"
echo ""

