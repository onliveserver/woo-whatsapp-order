# Force Reinstall Button - Quick Reference

## Location
**WooCommerce → WhatsApp Order** (top-right corner)

Next to "Check for Updates" button

## What It Does
Resets all plugin settings to defaults with one click.

## How to Use
1. Click the button
2. Confirm you want to reset settings
3. Wait for completion (~1 second)
4. Page reloads automatically

## What Gets Reset
- ✅ All WhatsApp settings (phone, templates, colors, etc.)
- ✅ GitHub update cache
- ✅ Plugin transients
- ✅ Cached data

## What Stays
- ✅ WordPress and WooCommerce data
- ✅ Products and orders
- ✅ Customer information

## Error Troubleshooting

| Error | Cause | Fix |
|-------|-------|-----|
| "Security check failed" | Nonce expired | Refresh page, try again |
| "Permission denied" | Not admin | Log in as admin |
| "Error during reinstallation" | PHP error | Check debug.log, see FORCE_REINSTALL_GUIDE.md |

## Debug Mode

Enable WordPress debug logging:

1. Edit `wp-config.php`
2. Add:
```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

3. Check errors in `wp-content/debug.log`

## Browser Console
Press F12 → Console tab to see detailed error messages

## Manual Alternative
If button fails:
1. Database: Delete `wp_onlive_wa_order_settings` option
2. Or: Deactivate/Reactivate plugin in Plugins page
3. Or: Contact support at support@onliveinfotech.com

## When to Use
- Settings corrupted
- Plugin behaving strangely
- After failed update
- Need fresh configuration
- Testing purposes

## NOT Recommended For
- Live store with active orders
- Recent important configuration
- Critical business hours

---

For complete details, see `docs/FORCE_REINSTALL_GUIDE.md`
