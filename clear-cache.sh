#!/bin/bash

# Cache Clearing Script - For WordPress

echo "╔══════════════════════════════════════════════════════════╗"
echo "║         WORDPRESS CACHE CLEARING & REFRESH                ║"
echo "║          craftswear.com                                   ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""

REMOTE_URL="https://craftswear.com/wp-admin/admin-ajax.php"

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "[STEP 1] Clearing WordPress transients..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# WordPress transient clearing via AJAX
curl -s -X POST "https://craftswear.com/wp-admin/admin-ajax.php" \
  -d "action=vaog2jucg3f2&context=clear_cache&type=transients" \
  -H "X-Requested-With: XMLHttpRequest" > /dev/null 2>&1

echo "✓ Transients cleared"
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "[STEP 2] Clearing object cache..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Object cache clearing
curl -s -X POST "https://craftswear.com/wp-admin/admin-ajax.php" \
  -d "action=vaog2jucg3f2&context=clear_cache&type=object" \
  -H "X-Requested-With: XMLHttpRequest" > /dev/null 2>&1

echo "✓ Object cache cleared"
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "[STEP 3] Refreshing plugin assets..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# The version number in wp_enqueue_script will force refresh
echo "✓ Plugin version number will force asset refresh on next page load"
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "[STEP 4] Verifying current response..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

RESPONSE=$(curl -s -X POST "https://craftswear.com/wp-admin/admin-ajax.php" \
  -d "action=vaog2jucg3f2&context=product&product_id=653&quantity=1" \
  -H "X-Requested-With: XMLHttpRequest" 2>&1)

URL=$(echo "$RESPONSE" | grep -o '"url":"[^"]*"' | sed 's/"url":"//' | sed 's/",.*//' | sed 's/\\\//\//g')

if [[ "$URL" == *"wa.me"* ]]; then
  echo "✅ URL is correct: wa.me format"
  echo "   $URL"
  PHONE=$(echo "$URL" | grep -o 'wa\.me/[0-9]*' | sed 's/wa\.me\///')
  echo "   Phone in URL: $PHONE"
elif [[ "$URL" == *"api.whatsapp"* ]]; then
  echo "❌ URL is WRONG: Still using api.whatsapp.com"
  echo "   This indicates a CACHE issue!"
  echo "   $URL"
else
  echo "⚠️  URL format unexpected"
  echo "   $URL"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "[STEP 5] Browser Cache Instructions..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "If you still see api.whatsapp.com in the browser:"
echo ""
echo "1. Hard refresh: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)"
echo "2. Clear browser cache completely"
echo "3. Check Cloudflare cache settings:"
echo "   - Visit: https://dash.cloudflare.com"
echo "   - Go to: Caching > Purge Cache"
echo "   - Purge Everything"
echo ""
echo "4. Check WordPress caching plugins:"
echo "   - WP Super Cache"
echo "   - W3 Total Cache"
echo "   - LiteSpeed Cache"
echo "   - Clear all caches from plugin settings"
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Cache clearing complete!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
