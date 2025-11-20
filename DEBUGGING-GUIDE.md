# WhatsApp Order Plugin - Debugging Guide

## Quick Test Commands

### 1. Test Product AJAX Request
```bash
curl -X POST "https://craftswear.com/wp-admin/admin-ajax.php" \
  -d "action=vaog2jucg3f2&context=product&product_id=653&variation_id=1172&quantity=1" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-Requested-With: XMLHttpRequest" | jq '.'
```

### 2. Check All Settings
```bash
curl -s -X POST "https://craftswear.com/wp-admin/admin-ajax.php" \
  -d "action=vaog2jucg3f2&context=debug_settings" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-Requested-With: XMLHttpRequest" | jq '.debug.settings_retrieval.all_settings'
```

### 3. Check Phone Number Only
```bash
curl -s -X POST "https://craftswear.com/wp-admin/admin-ajax.php" \
  -d "action=vaog2jucg3f2&context=product&product_id=653&quantity=1" \
  -H "X-Requested-With: XMLHttpRequest" | jq '.debug.phone_tracking'
```

### 4. Check Template Processing
```bash
curl -s -X POST "https://craftswear.com/wp-admin/admin-ajax.php" \
  -d "action=vaog2jucg3f2&context=product&product_id=653&quantity=1" \
  -H "X-Requested-With: XMLHttpRequest" | jq '.debug.template_tracking'
```

### 5. Run Full Test Suite (from local machine)
```bash
cd /path/to/plugin
bash maketest-ajax.sh
```

## What Each Debug Field Means

### phone_tracking
- `phone_raw`: Phone as stored in database (with formatting characters)
- `phone_sanitized`: Phone with only digits and + signs
- `phone_for_url`: Phone ready for wa.me URL (no plus sign)
- `phone_present`: Is there a phone number in settings?
- `phone_valid`: Is the phone number valid for URL generation?

### template_tracking
- `template_enabled`: Is custom template feature turned on?
- `custom_template`: The template text as saved in settings
- `message_template_used`: Which template was actually used?

### settings_retrieval
- `get_option_exists`: Is WordPress get_option() function available?
- `all_settings`: All 16 setting fields currently saved
- `settings_count`: Total number of settings loaded

## Important Notes

⚠️ **AJAX Header Required**: The `X-Requested-With: XMLHttpRequest` header is **MANDATORY** for the bootstrap handler to respond. Without it, you'll get empty responses.

✅ **What Should Be In The URL**:
- Format: `https://wa.me/919100454045?text=...`
- Phone: `919100454045` (no plus sign, no country code prefix if already included)
- Message: URL-encoded message text

## Troubleshooting

### Getting empty responses?
- Check if you're sending `X-Requested-With: XMLHttpRequest` header
- Check server logs: `/var/log/apache2/error.log` or equivalent
- Check WordPress debug: Set `WP_DEBUG` to true in `wp-config.php`

### Phone not in URL?
- Check `debug.phone_tracking.phone_raw` - is it populated?
- Check `debug.phone_tracking.phone_valid` - should be true
- Check `debug.settings_retrieval.all_settings.phone` - should not be empty

### Template not working?
- Check `debug.template_tracking.template_enabled` - should be 1
- Check `debug.template_tracking.custom_template` - should have your template text
- Check `debug.message_info.final_message` - should show placeholders replaced

## Files in This Package

- `woo-whatsapp-order-pro.php` - Main plugin with bootstrap AJAX handler and debug code
- `maketest-ajax.sh` - Bash script to run 6 comprehensive tests against remote server
- `DEBUG-REPORT.md` - Current test results showing everything is working
- `DEBUGGING-GUIDE.md` - This file

## Recent Test Results

✅ **All Tests Passed** (November 20, 2025)
- Phone retrieved: +919100454045
- URL generated: https://wa.me/919100454045?text=...
- Template applied: Custom template used
- Settings loaded: 16 items
- Consistency: All 3 requests returned same URL

---

**Status**: Production Ready ✅
All debugging features enabled. Check `DEBUG-REPORT.md` for full results.
