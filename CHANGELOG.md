# [1.3.0] - 2025-11-20

### Changed
- **Removed all authentication (nonce) from WhatsApp message AJAX handler**: Now all users (including guests) can generate WhatsApp messages without error or authentication barrier.
- **Improved error handling**: Enhanced admin Force Reinstall tool and error messages for better UX.
- **Documentation and codebase cleanup**: Updated docs, removed test/debug files, and improved code comments.

**Breaking Changes**: None – Fully backward compatible

# Changelog

All notable changes to the Onlive WooCommerce WhatsApp Order plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2025-01-XX

### Added
- **Responsive Device Detection**: Automatically detects mobile/desktop and uses optimal WhatsApp endpoint
  - Desktop users get `web.whatsapp.com` (WhatsApp Web)
  - Mobile users get `api.whatsapp.com` or app link
  - Utilizes WordPress `wp_is_mobile()` function for detection
  
- **Guest User Support**: Non-logged-in users can now place WhatsApp orders
  - Fixed AJAX nonce verification to properly support `wp_ajax_nopriv` actions
  - Guest customers display as "Guest Customer" in message templates
  - Improved `prepare_replacements()` method to handle guest users
  
- **Blank Message Prevention**: Smart validation ensures WhatsApp always opens with content
  - Fallback messages when template variables are missing
  - Product context: "Hello, I am interested in this product."
  - Cart context: "I would like to place an order from your store."
  - Prevents empty messages from reaching WhatsApp

### Fixed
- **AJAX Security for Non-Logged-In Users**: Changed from `check_ajax_referer()` to `wp_verify_nonce()` in `handle_ajax_message()` method to properly support guest users while maintaining security
- **Blank Message Box Issue**: Added message validation to ensure content exists before generating WhatsApp URL
- **Customer Name Handling**: Improved logic to handle logged-in users, guest users, and custom customer names
- **Desktop WhatsApp Experience**: Desktop users now get WhatsApp Web instead of confusing mobile app prompts

### Changed
- **Documentation Structure**: Moved all documentation .md files to `/docs` folder for cleaner root directory
- **README.md**: Completely rewritten with comprehensive examples, tables, badges, and modern formatting
- **AJAX Handler**: Updated `handle_ajax_message()` in `frontend/class-frontend.php` to use `wp_verify_nonce()` instead of `check_ajax_referer()`
- **WhatsApp URL Generation**: Enhanced `get_whatsapp_url()` method with device detection logic
- **Message Generation**: Improved `generate_message()` method with validation and fallback messages

### Technical Details

#### Modified Files
1. **woo-whatsapp-order-pro.php**
   - Updated version to 1.2.0 (header and class property)
   - Enhanced `get_whatsapp_url()` with device detection (Line 363)
   - Improved `prepare_replacements()` for guest users (Lines 292-311)
   - Added message validation in `generate_message()` (Lines 270-279)

2. **frontend/class-frontend.php**
   - Modified `handle_ajax_message()` nonce verification (Line 346-348)
   - Changed from `check_ajax_referer()` to manual `wp_verify_nonce()` check

3. **README.md**
   - Complete rewrite with modern markdown formatting
   - Added badges, tables, and comprehensive examples
   - Included device detection logic table
   - Added troubleshooting section with v1.2.0 fixes

#### Code Changes

**Device Detection Logic**:
```php
// Detect if user is on mobile device
$is_mobile = wp_is_mobile();

switch ( $choice ) {
    case 'api':
        if ( $is_mobile ) {
            $endpoint = 'https://api.whatsapp.com/send?...'; // Opens app
        } else {
            $endpoint = 'https://web.whatsapp.com/send?...'; // Opens web
        }
        break;
}
```

**Guest User Support**:
```php
// For non-logged-in users, use "Guest" or their provided name
if ( $current_user && $current_user->ID ) {
    $customer_name = $current_user->display_name;
} elseif ( isset( $data['customer_name'] ) ) {
    $customer_name = $data['customer_name'];
} else {
    $customer_name = __( 'Guest Customer', 'onlive-wa-order' );
}
```

**Blank Message Prevention**:
```php
// Ensure message is not blank
if ( empty( $message ) || preg_match('/^\{\{[^}]+\}\}$/', $message ) ) {
    // Fallback to basic message
    $message = __( 'Hello, I am interested in this product.', 'onlive-wa-order' );
}
```

## [1.1.0] - 2024-12-31

### Added
- **Check for Updates Button**: Manual update check functionality in admin settings
  - Button in settings page header (top-right corner)
  - AJAX handler with proper security (nonce verification)
  - Real-time status messages (checking, up-to-date, update available)
  - CSS animations for loading states
  - JavaScript click handler with error handling

- **Update Detection System**: Enhanced GitHub updater functionality
  - Hourly automatic checks for new releases
  - Version comparison logic
  - WordPress transient caching
  - Admin notice for available updates

### Fixed
- **Version Number Consistency**: Updated version to 1.1.0 in both plugin header and class property
- **Update Not Working**: Documented that GitHub Release must be created (not just Git tag) for auto-updates to function

### Changed
- **Admin Settings UI**: Added update button to settings page header
- **CSS Styling**: Added button styles with WhatsApp green (#25d366) and animations
- **Documentation**: Created comprehensive guides explaining update system

### Technical Details

#### Modified Files
1. **woo-whatsapp-order-pro.php**
   - Updated version from 1.0.0 to 1.1.0
   - Line 6 (plugin header)
   - Line 37 (class property)

2. **admin/settings-page.php**
   - Added AJAX action: `wp_ajax_onlive_wa_check_updates`
   - Added button UI in `render_settings_page()`
   - Added JavaScript handler for button clicks
   - Added backend processor: `ajax_check_updates()`

3. **assets/css/admin.css**
   - Added button styling (#onlive-wa-check-updates-btn)
   - Added hover effects and loading animations

#### Documentation Created
- ACTION_REQUIRED.md
- ARCHITECTURE_DIAGRAM.md
- CHECK_UPDATES_COMPLETE.md
- CHECK_UPDATES_FEATURE.md
- CREATE_GITHUB_RELEASE.md
- DOCUMENTATION_INDEX.md
- FINAL_CHECKLIST.md
- FINAL_SUMMARY.md
- IMPLEMENTATION_SUMMARY.md
- PROJECT_COMPLETE.md
- QUICK_START.md
- STATUS_REPORT.md
- UPDATE_ISSUE_RESOLVED.md
- UPDATE_NOT_WORKING_FIX.md
- UPDATES.md

## [1.0.0] - 2024-12-01

### Added
- **Initial Release**: First public version of the plugin
- **Product Page Integration**: WhatsApp order buttons on WooCommerce product pages
- **Cart Page Integration**: Bulk ordering functionality from cart page
- **Template System**: Customizable message templates with dynamic variables
  - `{{product_name}}`, `{{product_price}}`, `{{product_quantity}}`
  - `{{product_variation}}`, `{{product_sku}}`, `{{product_link}}`
  - `{{cart_total}}`, `{{site_name}}`, `{{customer_name}}`
  
- **Admin Settings Panel**: Comprehensive configuration interface
  - General settings tab
  - Template settings tab
  - Styling options tab
  - Advanced settings tab
  
- **Multiple API Options**: Support for different WhatsApp endpoints
  - wa.me (default)
  - api.whatsapp.com
  - Custom gateway URL
  
- **GitHub Auto-Updater**: Automatic update notifications from GitHub releases
  - Checks `/releases/latest` endpoint
  - Version comparison
  - WordPress update integration
  
- **Security Features**:
  - AJAX nonce verification
  - Capability checks for admin functions
  - Input sanitization
  - Output escaping
  
- **Frontend Features**:
  - jQuery-based click handlers
  - AJAX message generation
  - Product variation support
  - Quantity selection
  
- **Styling Options**:
  - Customizable button colors
  - Hover effects
  - Icon size adjustment
  - Custom CSS support

### Technical Details

#### Core Files Structure
```
onlive-whatsapp-order/
├── woo-whatsapp-order-pro.php (Main plugin file)
├── admin/
│   └── settings-page.php (Admin interface)
├── frontend/
│   └── class-frontend.php (Frontend button rendering)
├── includes/
│   ├── class-github-updater.php (Auto-update system)
│   └── class-template-parser.php (Template engine)
├── assets/
│   ├── css/ (Stylesheets)
│   └── js/ (JavaScript files)
└── languages/ (Translation files)
```

#### Key Features
- WordPress 6.0+ compatibility
- WooCommerce 5.0+ compatibility
- PHP 7.4+ requirement
- Translation ready (i18n support)
- WPCS coding standards compliant

---

## Upgrade Guide

### From 1.1.0 to 1.2.0

1. **Backup Your Site**: Always backup before updating
2. **Update Plugin**: Update via WordPress admin or manually replace files
3. **Clear Cache**: Clear all caches (WordPress, browser, CDN)
4. **Test Guest Orders**: Verify non-logged-in users can place orders
5. **Test Device Detection**: Check on both mobile and desktop devices
6. **Review Settings**: No settings changes required, all existing configs preserved

**Breaking Changes**: None - Fully backward compatible

**New Capabilities**:
- Guest users can now place orders (previously blocked)
- Desktop users get better WhatsApp Web experience
- Blank messages automatically prevented

### From 1.0.0 to 1.1.0

1. **Update Plugin Files**: Replace all files with version 1.1.0
2. **Check Settings**: Go to WooCommerce → WhatsApp Order
3. **Test Update Button**: Click "Check for Updates" button to verify functionality
4. **Create GitHub Release**: If using auto-updates, ensure GitHub Release is created (not just tag)

**Breaking Changes**: None

---

## Support & Contributing

- **Issues**: Report bugs at https://github.com/onliveserver/woo-whatsapp-order/issues
- **Email**: support@onliveinfotech.com
- **Documentation**: See `/docs` folder for detailed guides

---

[Unreleased]: https://github.com/onliveserver/woo-whatsapp-order/compare/v1.2.0...HEAD
[1.2.0]: https://github.com/onliveserver/woo-whatsapp-order/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/onliveserver/woo-whatsapp-order/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/onliveserver/woo-whatsapp-order/releases/tag/v1.0.0
