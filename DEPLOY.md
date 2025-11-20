# Deployment Guide for Template Variables Fix

## Issue Fixed
Template variables (`{{product_price}}`, `{{product_variation}}`, `{{product_link}}`, etc.) were not being populated when generating WhatsApp messages.

## Root Cause
- The product price retrieval was failing due to empty values being treated as falsy
- Variation attributes were not being properly formatted
- Product link was not being included even when the setting was enabled

## Changes Made
1. **Bootstrap Handler (woo-whatsapp-order-pro.php)**:
   - Added robust price retrieval with multiple fallbacks (get_price → get_regular_price → get_sale_price)
   - Fixed null/empty/false checking to properly handle zero prices
   - Improved variation attribute formatting with ucfirst() for proper capitalization
   - Added strict equality checks instead of falsy checks

2. **Frontend AJAX Handler (frontend/class-frontend.php)**:
   - Enhanced `prepare_product_data()` with multiple price retrieval fallbacks
   - Added try-catch blocks for WooCommerce functions
   - Improved variation formatting
   - Better handling of guest user pricing

3. **Main Plugin File**:
   - Product link is now properly included/excluded based on setting
   - Template variables are all properly initialized

## Deployment Steps

### Option 1: Manual Git Pull (Recommended for Server with Git)
```bash
cd /path/to/wp-content/plugins/onlive-whatsapp-order
git pull origin main
```

### Option 2: Manual File Upload (For FTP/cPanel)
Download these files and upload to your server:
- `woo-whatsapp-order-pro.php` (MAIN PLUGIN FILE - CRITICAL)
- `frontend/class-frontend.php`
- `includes/class-template-parser.php`

### Option 3: Upload via WordPress Admin
1. Go to Plugins
2. Click "Deactivate" for "Onlive WooCommerce WhatsApp Order"
3. Click "Delete"
4. Click "Add New"
5. Click "Upload Plugin"
6. Select the plugin zip file
7. Click "Activate"

## Verification Steps
After deployment, test with the following:

1. **Enable Template Settings**:
   - Go to Settings > Onlive WhatsApp Order
   - Enable "Template Builder"
   - Enable "Include Product Link"
   - Use custom template: `Hi! Interested in {{product_name}} – {{product_variation}}. Qty: {{product_quantity}} • Price: {{product_price}} Please send more info.`
   - Save settings

2. **Test AJAX Endpoint** (via command line):
```bash
curl -X POST "https://yoursite.com/wp-admin/admin-ajax.php" \
  -d "action=vaog2jucg3f2&context=product&product_id=653&variation_id=1172&quantity=2" \
  -H "X-Requested-With: XMLHttpRequest"
```

3. **Check Response**:
Look for:
- `"product_price"` should have currency formatted price (e.g., "₹14,999.00")
- `"product_variation"` should have variation details (e.g., "Size: Large, Color: Red")
- `"product_link"` should have the product URL
- `"final_message"` should have all placeholders replaced

4. **Test on Frontend**:
- Visit a product page
- Check that the WhatsApp button shows
- Click the button and verify the message preview shows prices and variations

## Cache Clearing (Important!)
After uploading new files, clear all caches:

1. **Browser Cache**:
   - Ctrl+Shift+Del (or Cmd+Shift+Del on Mac)
   - Clear all browsing data

2. **WordPress Cache** (if using cache plugin):
   - Go to Settings > LiteSpeed Cache (or your cache plugin)
   - Click "Purge All"

3. **Server File Cache**:
   ```bash
   # If using nginx
   sudo service nginx reload
   
   # If using Apache
   sudo service apache2 reload
   ```

## Troubleshooting

### Still seeing empty prices/variations
1. Check WooCommerce product settings - ensure prices are set
2. Verify variation attributes are defined
3. Clear all caches again
4. Check browser Network tab - what status code is AJAX returning?

### Product link not showing
1. Verify "Include Product Link" is enabled in settings
2. Check that `include_product_link` setting value is "1"
3. Verify the generated message includes the product URL

### Message still showing empty placeholders
1. Check that template is properly formatted with {{double_braces}}
2. Verify "Template Builder" is enabled
3. Test with default message (no custom template)

## Files Modified
- `woo-whatsapp-order-pro.php` - Main bootstrap handler
- `frontend/class-frontend.php` - Frontend AJAX handler
- Commits: b1db301, 46b712e

## Version
Updated to version 1.4.2 (previous: 1.4.1)

