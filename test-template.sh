#!/bin/bash

# Template Variable Test Script

REMOTE_URL="https://craftswear.com/wp-admin/admin-ajax.php"

echo ""
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║        TEMPLATE VARIABLE TEST - Template Processing            ║"
echo "║                   craftswear.com                                ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "[TEST 1] Template Settings Status"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

RESPONSE=$(curl -s -X POST "$REMOTE_URL" \
  -d "action=vaog2jucg3f2&context=debug_settings" \
  -H "X-Requested-With: XMLHttpRequest" 2>&1)

TEMPLATE_ENABLED=$(echo "$RESPONSE" | grep -o '"template_enabled":[^,]*' | sed 's/"template_enabled"://')
echo "Template Enabled: $TEMPLATE_ENABLED"

INCLUDE_PRODUCT_LINK=$(echo "$RESPONSE" | grep -o '"include_product_link":[^,}]*' | sed 's/"include_product_link"://')
echo "Include Product Link: $INCLUDE_PRODUCT_LINK"

TEMPLATE_TEXT=$(echo "$RESPONSE" | grep -o '"message_template":"[^"]*' | sed 's/"message_template":"//' | cut -c1-80)
echo "Custom Template (first 80 chars): $TEMPLATE_TEXT..."

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "[TEST 2] Product Request with Template"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

PAYLOAD="action=vaog2jucg3f2&context=product&product_id=653&variation_id=1172&quantity=2"

RESPONSE=$(curl -s -X POST "$REMOTE_URL" \
  -d "$PAYLOAD" \
  -H "X-Requested-With: XMLHttpRequest" 2>&1)

echo "Product: 653 | Variation: 1172 | Quantity: 2"
echo ""

# Extract values
PRODUCT_NAME=$(echo "$RESPONSE" | grep -o '"product_name":"[^"]*' | sed 's/"product_name":"//' | head -1)
echo "✓ Product Name: $PRODUCT_NAME"

PRODUCT_PRICE=$(echo "$RESPONSE" | grep -o '"product_price":"[^"]*' | sed 's/"product_price":"//' | head -1)
echo "✓ Product Price: $PRODUCT_PRICE"

PRODUCT_LINK=$(echo "$RESPONSE" | grep -o '"product_link":"[^"]*' | sed 's/"product_link":"//' | head -1)
if [ ! -z "$PRODUCT_LINK" ]; then
  echo "✓ Product Link: $PRODUCT_LINK"
else
  echo "✗ Product Link: NOT SET"
fi

TEMPLATE_USED=$(echo "$RESPONSE" | grep -o '"message_template_used":"[^"]*' | sed 's/"message_template_used":"//' | cut -c1-80)
echo "✓ Template Used: $TEMPLATE_USED..."

FINAL_MESSAGE=$(echo "$RESPONSE" | grep -o '"final_message":"[^"]*' | sed 's/"final_message":"//' | head -1)
echo "✓ Final Message:"
echo "   $FINAL_MESSAGE"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "[TEST 3] Verify All Template Variables"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

VARS=("product_name" "product_price" "product_quantity" "product_variation" "product_sku" "product_link" "site_name" "customer_name")

for var in "${VARS[@]}"; do
  VALUE=$(echo "$RESPONSE" | grep -o "\"$var\":\"[^\"]*\"" | sed "s/\"$var\":\"//" | sed 's/"$//')
  if [ ! -z "$VALUE" ]; then
    echo "✓ {{$var}}: $VALUE"
  else
    echo "✗ {{$var}}: NOT FOUND"
  fi
done

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "[TEST 4] Check WhatsApp URL"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

URL=$(echo "$RESPONSE" | grep -o '"url":"[^"]*' | sed 's/"url":"//' | sed 's/\\\//\//g' | head -1)
echo "Generated URL:"
echo "   $URL"

if [[ "$URL" == *"wa.me"* ]]; then
  echo ""
  echo "✓ URL format: CORRECT (wa.me)"
  
  PHONE=$(echo "$URL" | grep -o 'wa\.me/[^?]*' | sed 's/wa\.me\///')
  echo "✓ Phone: $PHONE"
  
  # Check if product link is in message
  if [[ "$FINAL_MESSAGE" == *"craftswear.com"* ]]; then
    echo "✓ Product link included in message"
  else
    echo "⚠ Product link: May not be included (check if enabled)"
  fi
else
  echo "✗ URL format: WRONG"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Template Test Complete"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
