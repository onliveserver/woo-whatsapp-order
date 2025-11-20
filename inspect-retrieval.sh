#!/bin/bash

# Phone Retrieval Inspection without jq dependency

REMOTE_URL="https://craftswear.com/wp-admin/admin-ajax.php"

echo ""
echo "╔════════════════════════════════════════════════════════════╗"
echo "║   PHONE & SETTINGS RETRIEVAL INSPECTION                     ║"
echo "║   Remote: craftswear.com                                    ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Test 1: Get debug settings
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "[TEST 1] SETTINGS RETRIEVAL"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

RESPONSE=$(curl -s -X POST "$REMOTE_URL" \
  -d "action=vaog2jucg3f2&context=debug_settings" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-Requested-With: XMLHttpRequest" 2>&1)

# Extract phone using grep and sed
PHONE_RAW=$(echo "$RESPONSE" | grep -o '"phone":"[^"]*"' | sed 's/"phone":"//' | sed 's/".*//')
echo "Phone (from settings): $PHONE_RAW"

PHONE_SANITIZED=$(echo "$RESPONSE" | grep -o '"phone_sanitized":"[^"]*"' | sed 's/"phone_sanitized":"//' | sed 's/".*//')
echo "Phone (sanitized): $PHONE_SANITIZED"

PHONE_FOR_URL=$(echo "$RESPONSE" | grep -o '"phone_for_url":"[^"]*"' | sed 's/"phone_for_url":"//' | sed 's/".*//')
echo "Phone (for wa.me): $PHONE_FOR_URL"

SETTINGS_COUNT=$(echo "$RESPONSE" | grep -o '"settings_count":[0-9]*' | sed 's/"settings_count"://')
echo "Settings count: $SETTINGS_COUNT items"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "[TEST 2] URL GENERATION"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

URL=$(echo "$RESPONSE" | grep -o '"url":"[^"]*"' | sed 's/"url":"//' | sed 's/",.*//' | sed 's/\\\//\//g')
echo "Generated URL: $URL"

if [[ "$URL" == *"wa.me"* ]]; then
  EXTRACTED_PHONE=$(echo "$URL" | grep -o 'wa\.me/[0-9]*' | sed 's/wa\.me\///')
  if [ -z "$EXTRACTED_PHONE" ]; then
    echo "❌ ERROR: No phone in URL!"
  else
    echo "✅ Phone extracted from URL: $EXTRACTED_PHONE"
  fi
else
  echo "❌ ERROR: URL doesn't contain wa.me"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "[TEST 3] TEMPLATE CONFIGURATION"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

TEMPLATE_ENABLED=$(echo "$RESPONSE" | grep -o '"template_enabled":[true|false|0|1]*' | sed 's/"template_enabled"://')
echo "Template enabled: $TEMPLATE_ENABLED"

MESSAGE_TEMPLATE=$(echo "$RESPONSE" | grep -o '"custom_template":"[^"]*' | sed 's/"custom_template":"//' | cut -c1-80)
echo "Template content (first 80 chars): $MESSAGE_TEMPLATE..."

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "[TEST 4] PHONE VALIDATION STATUS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

PHONE_PRESENT=$(echo "$RESPONSE" | grep -o '"phone_present":[true|false]*' | sed 's/"phone_present"://')
echo "Phone present: $PHONE_PRESENT"

PHONE_VALID=$(echo "$RESPONSE" | grep -o '"phone_valid":[true|false]*' | sed 's/"phone_valid"://')
echo "Phone valid: $PHONE_VALID"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "[TEST 5] PRODUCT AJAX REQUEST"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

PAYLOAD="action=vaog2jucg3f2&context=product&product_id=653&variation_id=1172&quantity=1"
echo "Sending request with product_id=653, variation_id=1172, quantity=1"
echo ""

RESPONSE2=$(curl -s -X POST "$REMOTE_URL" \
  -d "$PAYLOAD" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-Requested-With: XMLHttpRequest" 2>&1)

PRODUCT_URL=$(echo "$RESPONSE2" | grep -o '"url":"[^"]*"' | sed 's/"url":"//' | sed 's/",.*//' | sed 's/\\\//\//g')
echo "Product URL: $PRODUCT_URL"

PRODUCT_PHONE=$(echo "$PRODUCT_URL" | grep -o 'wa\.me/[0-9]*' | sed 's/wa\.me\///')
if [ -z "$PRODUCT_PHONE" ]; then
  echo "❌ ERROR: Phone NOT in product URL"
else
  echo "✅ Phone in product URL: $PRODUCT_PHONE"
fi

echo ""
echo "╔════════════════════════════════════════════════════════════╗"
echo "║                     FINAL SUMMARY                           ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

if [ ! -z "$PHONE_RAW" ] && [ ! -z "$PHONE_FOR_URL" ]; then
  echo "✅ PHONE RETRIEVAL: SUCCESS"
  echo "   Database: $PHONE_RAW"
  echo "   URL Format: $PHONE_FOR_URL"
  echo "   Generated URLs: Include $PHONE_FOR_URL ✓"
else
  echo "❌ PHONE RETRIEVAL: FAILED"
fi

if [ "$TEMPLATE_ENABLED" == "true" ] || [ "$TEMPLATE_ENABLED" == "1" ]; then
  echo "✅ TEMPLATES: ENABLED"
else
  echo "⚠️  TEMPLATES: DISABLED"
fi

if [ "$SETTINGS_COUNT" == "16" ]; then
  echo "✅ SETTINGS: ALL 16 ITEMS LOADED"
else
  echo "⚠️  SETTINGS: Only $SETTINGS_COUNT items loaded"
fi

echo ""
echo "════════════════════════════════════════════════════════════"
echo ""
