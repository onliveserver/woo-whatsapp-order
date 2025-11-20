# Guest User AJAX Issue - Debugging Guide

## Problem
Logged-out users see "Unable to build the WhatsApp message. Please try again." while logged-in users work fine.

## Root Causes & Fixes Applied

### 1. **DONE: AJAX Registration**
✅ **Fixed** - Both `wp_ajax_onlive_wa_build_message` and `wp_ajax_nopriv_onlive_wa_build_message` are properly registered

### 2. **DONE: Nonce Verification Removed**
✅ **Fixed** - No nonce checks blocking guest users (they were removed to allow public access)

### 3. **DONE: Header Management**
✅ **Improved** - Headers are now set at the start of `handle_ajax_message()`:
- `Content-Type: application/json`
- `X-Requested-With: XMLHttpRequest`
- `Cache-Control: no-store, no-cache`
- All set BEFORE any processing

### 4. **DONE: Redirect Prevention**
✅ **Improved** - Multiple layers of protection:
- Early detection in `prevent_ajax_redirect()` using `is_our_ajax_request_early()`
- Checks for `X-Requested-With: xmlhttprequest` header even before DOING_AJAX is defined
- `remove_action()` calls at start of AJAX handler

### 5. **DONE: Product Data Retrieval**
✅ **Improved** - Added fallbacks for guest users:
- Try-catch blocks around WooCommerce functions
- Fallback price retrieval: `$product->get_price()` if `wc_get_price_to_display()` fails
- Better error messages when product retrieval fails

### 6. **DONE: Error Handling**
✅ **Improved** - Better error reporting:
- Specific error codes for different failures
- More detailed error messages in debug mode
- Full exception trace logged in `wp-content/debug.log`

## Debugging Steps

### Step 1: Enable Debug Mode
Add to `wp-config.php`:
```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

### Step 2: Test AJAX Request
Open browser console (F12) and run:
```javascript
// Check if AJAX endpoint is accessible
fetch('/wp-admin/admin-ajax.php', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: new FormData(new FormData()).append('action', 'onlive_wa_ping')
})
.then(r => r.json())
.then(d => console.log('AJAX Test:', d))
.catch(e => console.error('AJAX Error:', e));
```

### Step 3: Check Error Log
Look at `/wp-content/debug.log` for:
- "WhatsApp AJAX Request:" - confirms request was received
- "WhatsApp AJAX Exception:" - shows what went wrong
- "Product error" - product retrieval failed
- "Cart is empty" - cart issues

### Step 4: Check Server Configuration

**For nginx:**
Ensure admin-ajax.php passes through (not blocked by security rules)

**For Apache:**
Check `.htaccess` doesn't block AJAX requests:
```apache
# Bad - blocks everything
<FilesMatch "admin-ajax.php">
    Deny from all
</FilesMatch>

# Good - allows AJAX
```

### Step 5: Browser Network Tab
1. Open DevTools → Network tab
2. Click WhatsApp button as guest
3. Look for `/wp-admin/admin-ajax.php` request:
   - **Status 200**: Request succeeded but response error
   - **Status 302/301**: Redirect happening (should be fixed now)
   - **Status 404**: File not found
   - **Status 403**: Permission denied

## Common Issues & Solutions

### Issue: Status 302 Redirect to /404
**Cause**: WordPress is treating AJAX as a page request
**Fixed By**: Early removal of `redirect_canonical` in AJAX handler

### Issue: Status 404 in response
**Cause**: WordPress 404 template is rendering instead of AJAX response
**Fixed By**: `filter_status_header()` and `handle_404_override()` methods

### Issue: "Product not found" error
**Cause**: `wc_get_product()` returning null for guest users
**Solutions**:
1. Ensure product is published (not draft)
2. Check product is available for guest viewing
3. Verify WooCommerce permissions allow guest access

### Issue: Product price showing as "$0" or "Unknown"
**Cause**: `wc_get_price_to_display()` requires user permissions
**Fixed By**: Fallback to `$product->get_price()` in try-catch

## Network Conditions to Test

1. **Logged-Out User**: Clear cookies, test in private window
2. **Different User Roles**: Test as subscriber, customer, admin
3. **Different Products**: Test simple, variable, variation products
4. **Different Browsers**: Chrome, Firefox, Safari (check compatibility)
5. **Mobile**: Test on actual mobile device or DevTools mobile view

## Key Code Files

- **Frontend Handler**: `frontend/class-frontend.php` (lines 462-550)
  - `handle_ajax_message()`: Main AJAX handler
  - `prepare_product_data()`: Product data retrieval with fallbacks
  - `is_our_ajax_request_early()`: Early AJAX detection
  
- **JavaScript Handler**: `assets/js/frontend.js` (lines 90-130)
  - Error handler catches 302, 301, 307 redirects
  - Network error handling
  - Specific messages for each HTTP status

## Quick Test Checklist

- [ ] Can logged-out user see WhatsApp button on product?
- [ ] Does button respond to click (shows loading spinner)?
- [ ] Does `/wp-admin/admin-ajax.php` ping successfully?
- [ ] Is "X-Requested-With: xmlhttprequest" header being sent?
- [ ] Does response have status 200 (not 302/404)?
- [ ] Does response have valid JSON with `success` and `data.url`?
- [ ] Does `data.url` contain valid WhatsApp link?
- [ ] Does WhatsApp open when URL is clicked?

## Related Files
- `.github/workflows/` - CI/CD configuration
- `admin/settings-page.php` - Admin configuration (for admins only)
- `includes/class-github-updater.php` - Update checker
- `woo-whatsapp-order-pro.php` - Main plugin file

## Next Steps if Issue Persists

1. **Check Server Logs**: `/var/log/apache2/error.log` or `/var/log/nginx/error.log`
2. **Enable PHP Error Display**: Temporarily set `WP_DEBUG_DISPLAY` to true
3. **Test With Plugin Deactivated**: Disable all other plugins
4. **Test in Safe Mode**: Use WooCommerce troubleshooting tools
5. **Check Product Permissions**: Ensure products don't have visibility restrictions
6. **Verify WooCommerce Version**: Ensure compatible version (3.0+)
7. **Test REST API**: WooCommerce REST API should also be accessible

## Version Info
- Plugin: v1.3.0+
- Last Updated: November 20, 2025
- Tested With: WordPress 6.0+, WooCommerce 6.0+
