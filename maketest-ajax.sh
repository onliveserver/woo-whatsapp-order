#!/bin/bash

# Color codes for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

REMOTE_URL="https://craftswear.com/wp-admin/admin-ajax.php"

# Required headers for AJAX to trigger bootstrap handler
AJAX_HEADERS="-H 'Content-Type: application/x-www-form-urlencoded' -H 'X-Requested-With: XMLHttpRequest'"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}WhatsApp Order AJAX Debug Test${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Test 1: Get all saved settings
echo -e "${YELLOW}[TEST 1] Fetching all plugin settings from database...${NC}"
curl -s -X POST "$REMOTE_URL" \
  -d "action=vaog2jucg3f2&context=debug_settings" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-Requested-With: XMLHttpRequest" | jq '.' 2>/dev/null || curl -s -X POST "$REMOTE_URL" -d "action=vaog2jucg3f2&context=debug_settings" -H "X-Requested-With: XMLHttpRequest"
echo ""
echo ""

# Test 2: Product AJAX request (same as user's request)
echo -e "${YELLOW}[TEST 2] Testing product AJAX request...${NC}"
PAYLOAD="action=vaog2jucg3f2&context=product&product_id=653&variation_id=1172&quantity=1&variations=%7B%22pa_sole%22%3A%22rubber%22%2C%22pa_size%22%3A%228%22%7D&nonce=fc98393499"
echo -e "${BLUE}Sending: $PAYLOAD${NC}"
echo ""

RESPONSE=$(curl -s -X POST "$REMOTE_URL" -d "$PAYLOAD" -H "Content-Type: application/x-www-form-urlencoded" -H "X-Requested-With: XMLHttpRequest")
echo -e "${GREEN}Response:${NC}"
echo "$RESPONSE" | jq '.' 2>/dev/null || echo "$RESPONSE"
echo ""
echo ""

# Test 3: Check if phone is in URL
echo -e "${YELLOW}[TEST 3] Checking if phone number is in generated URL...${NC}"
PHONE_CHECK=$(echo "$RESPONSE" | grep -o '"url":"[^"]*"' | grep -o 'wa\.me/[0-9+]*' || echo "NO_PHONE_FOUND")
echo -e "${BLUE}Phone in URL: $PHONE_CHECK${NC}"
echo ""
echo ""

# Test 4: Check debug info
echo -e "${YELLOW}[TEST 4] Debug information from response:${NC}"
echo "$RESPONSE" | jq '.debug' 2>/dev/null || echo "No debug info available"
echo ""
echo ""

# Test 5: Get all settings via debug action
echo -e "${YELLOW}[TEST 5] Direct settings check...${NC}"
curl -s -X POST "$REMOTE_URL" \
  -d "action=vaog2jucg3f2&context=check_settings&debug=full" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-Requested-With: XMLHttpRequest" | jq '.' 2>/dev/null || curl -s -X POST "$REMOTE_URL" -d "action=vaog2jucg3f2&context=check_settings&debug=full" -H "X-Requested-With: XMLHttpRequest"
echo ""
echo ""

# Test 6: Multiple requests to verify consistency
echo -e "${YELLOW}[TEST 6] Sending multiple requests to check consistency...${NC}"
for i in {1..3}; do
  echo -e "${BLUE}Request $i:${NC}"
  RESPONSE=$(curl -s -X POST "$REMOTE_URL" -d "$PAYLOAD" -H "Content-Type: application/x-www-form-urlencoded" -H "X-Requested-With: XMLHttpRequest")
  echo "$RESPONSE" | jq '.url // .phone_tracking.phone_for_url // "ERROR"' 2>/dev/null || echo "$RESPONSE"
  sleep 1
done

echo ""
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}Test completed!${NC}"
echo -e "${BLUE}========================================${NC}"
