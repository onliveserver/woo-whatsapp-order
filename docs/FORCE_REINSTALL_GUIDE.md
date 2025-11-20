# Force Reinstall Button - Debug & Usage Guide

## What the Button Does

The "Force Reinstall" button in WooCommerce → WhatsApp Order settings allows admins to:

1. **Reset all plugin settings** to default values
2. **Clear cached data** from transients and caches
3. **Reinitialize the plugin** database tables (if applicable)
4. **Restore default functionality** when settings are corrupted

## How to Use

### Step-by-Step

1. Go to **WordPress Admin → WooCommerce → WhatsApp Order**
2. Look for the **"Force Reinstall"** button (next to "Check for Updates")
3. Click the button
4. Confirm the warning: "This will reset all plugin data and settings. Are you sure?"
5. Wait for the reinstallation to complete
6. Page will automatically reload with fresh settings

## What Gets Reset

### Deleted
- ✅ All plugin settings and options
- ✅ GitHub update cache
- ✅ WordPress transients
- ✅ Plugin object cache

### Preserved  
- ✅ WordPress data
- ✅ WooCommerce products
- ✅ Customer data
- ✅ Orders and transactions

## Troubleshooting

### Issue: "Error during reinstallation. Please try again."

#### Check Browser Console
1. Press **F12** to open Developer Tools
2. Click **Console** tab
3. Look for error messages
4. Common errors and solutions:

```javascript
// ERROR: "nonce" is undefined
// SOLUTION: Refresh page, clear browser cache
// The nonce is generated fresh for security

// ERROR: 403 Forbidden
// SOLUTION: Ensure user has admin/manage_options capability

// ERROR: 500 Internal Server Error
// SOLUTION: Check WordPress debug log
```

#### Check WordPress Debug Log

1. Open `wp-config.php` (in WordPress root)
2. Add or ensure these lines exist:
```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

3. Check log file at: `wp-content/debug.log`
4. Look for errors containing "onlive_wa" or "force_reinstall"

#### Manual Reinstall (If Button Doesn't Work)

If the button keeps failing, manually reset via database:

**Option 1: WordPress Admin**
1. Go to **Tools → Site Health**
2. Look for "Onlive WhatsApp Order" data
3. Or use a database plugin to delete options

**Option 2: Database Query (phpMyAdmin)**
```sql
DELETE FROM wp_options 
WHERE option_name LIKE 'onlive_wa%';

DELETE FROM wp_options 
WHERE option_name IN ('onlive_wa_github_version', 'onlive_wa_github_release');
```

**Option 3: FTP/File Manager**
1. Connect via SFTP
2. Deactivate plugin via WordPress admin
3. Reactivate plugin
4. Plugin will reinitialize with defaults

### Issue: Page Doesn't Reload After Reset

**Solution:**
1. Manually refresh the page (F5 or Ctrl+R)
2. Check if settings are reset (they should be)
3. Reconfigure your WhatsApp settings if needed

### Issue: Some Settings Not Resetting

**Solution:**
Settings are stored in multiple places:
- WordPress options table
- PHP transients (cache)
- WordPress object cache
- Theme customizer (if used)

Try this sequence:
1. Force Reinstall (clears options)
2. Clear all caching plugins
3. Clear browser cache (Ctrl+Shift+Delete)
4. Deactivate/Reactivate plugin

## Security

### Why Nonce Verification?

The button uses WordPress nonce verification to:
- ✅ Prevent CSRF (Cross-Site Request Forgery) attacks
- ✅ Ensure request came from your own site
- ✅ Require admin authentication

### Nonce Details
- **Created on:** Each page load (fresh security token)
- **Valid for:** 24 hours (default WordPress)
- **Action:** `onlive_wa_check_updates_nonce`

### If Nonce Fails

1. **Refresh the page** - Creates new nonce
2. **Clear cookies** - Clears nonce session
3. **Check user roles** - Must have "manage_options" capability
4. **Disable security plugins** - Some plugins interfere with nonces

## Technical Details

### AJAX Endpoint
- **Action:** `onlive_wa_force_reinstall`
- **Method:** POST
- **Auth:** Requires `manage_options` capability
- **Security:** Nonce verified

### What the Handler Does

```php
1. Verify nonce and user permissions
2. Delete option: onlive_wa_order_settings
3. Delete transient: onlive_wa_github_version
4. Delete transient: onlive_wa_github_release
5. Clear WP cache: onlive_wa_settings
6. Run activate hook: onlive_wa_order_pro_activate()
7. Refresh plugin settings object
8. Send success response
```

### Response Format

**Success:**
```json
{
  "success": true,
  "data": "✓ Plugin has been reinstalled successfully. All default settings have been restored."
}
```

**Error:**
```json
{
  "success": false,
  "data": "Security check failed. Please refresh the page and try again."
}
```

## When to Use Force Reinstall

### ✅ Use When:
- Settings are corrupted or acting strange
- Plugin behaves unexpectedly after failed update
- Need to start fresh with default configuration
- WhatsApp buttons stop working for unknown reason
- Testing plugin initialization

### ❌ Don't Use When:
- You have important custom configuration
- Currently running live orders
- Made recent changes to settings
- Just updated the plugin

### Before Using
**Backup Your Settings:**
1. Take screenshot of current settings
2. Note your WhatsApp phone number
3. Copy any custom CSS or templates
4. Note button positions and customizations

## Recovery Steps

If something goes wrong:

1. **Go to Settings Page**
   - WooCommerce → WhatsApp Order
   - Check if all settings are at defaults

2. **Reconfigure**
   - Enter phone number again
   - Re-enable button
   - Recreate custom templates if needed

3. **Test**
   - Add product to cart
   - Click WhatsApp button
   - Verify it works

4. **Contact Support**
   - Email: support@onliveinfotech.com
   - Include error messages from browser console
   - Describe what happened before issue

## Performance Impact

- **Duration:** Less than 1 second typically
- **Database:** 4-5 small delete operations
- **Performance:** No impact on site performance
- **User Impact:** None (only admin-facing)
- **Downtime:** None (async background operation)

---

**Questions?** Check browser console for detailed error messages or contact support!
