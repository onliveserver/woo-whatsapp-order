# Onlive WooCommerce WhatsApp Order

[![Version](https://img.shields.io/badge/version-1.3.0-blue.svg)](https://github.com/onliveserver/woo-whatsapp-order)
[![WordPress](https://img.shields.io/badge/wordpress-6.0%2B-blue.svg)](https://wordpress.org/)
[![WooCommerce](https://img.shields.io/badge/woocommerce-5.0%2B-purple.svg)](https://woocommerce.com/)
[![PHP](https://img.shields.io/badge/php-7.4%2B-green.svg)](https://php.net/)

A powerful WordPress plugin that adds customizable WhatsApp "Order Now" buttons to your WooCommerce product and cart pages. Allow customers to quickly place orders via WhatsApp with advanced message templates, responsive device detection, and seamless integration.

## 🚀 Key Features

- ✅ **Product & Cart Integration** - WhatsApp buttons on product pages and cart
- ✅ **Smart Device Detection** - Automatically uses optimal WhatsApp link for mobile/desktop
- ✅ **Guest Checkout Support** - Works for both logged-in and non-logged-in users
- ✅ **Customizable Templates** - Create personalized message templates with dynamic variables
- ✅ **Multiple API Options** - Choose between wa.me, api.whatsapp.com, web.whatsapp.com, or custom gateway
- ✅ **Blank Message Prevention** - Smart validation ensures messages always have content
- ✅ **Auto-Updates** - Automatic update notifications from GitHub releases
- ✅ **Security First** - Built-in nonce verification and capability checks
- ✅ **Translation Ready** - Full i18n support

## 📋 Requirements

- **WordPress**: 6.0 or higher
- **WooCommerce**: 5.0 or higher
- **PHP**: 7.4 or higher

## 📦 Installation

### Via WordPress Admin

1. Download the plugin ZIP file
2. Navigate to **WordPress Admin → Plugins → Add New**
3. Click **Upload Plugin** and select the ZIP file
4. Click **Install Now** and then **Activate**

### Manual Installation

1. Download the plugin ZIP file
2. Extract to `wp-content/plugins/onlive-whatsapp-order/`
3. Navigate to **WordPress Admin → Plugins**
4. Find **Onlive WooCommerce WhatsApp Order** and click **Activate**

### Via GitHub

```bash
cd wp-content/plugins/
git clone https://github.com/onliveserver/woo-whatsapp-order.git onlive-whatsapp-order
```

## ⚙️ Configuration

Navigate to **WooCommerce → WhatsApp Order** in your WordPress admin:

### General Settings

| Setting | Description |
|---------|-------------|
| **Enable Button** | Turn the WhatsApp button on/off globally |
| **Phone Number** | Enter your WhatsApp business number with country code (e.g., `+1234567890`) |
| **Button Text** | Customize the button label (default: "Order via WhatsApp") |
| **Button Position** | Choose where to display the button on product pages |
| **API Choice** | Select wa.me, api.whatsapp.com, or custom gateway |

### Template Settings

Configure custom message templates with these variables:

| Variable | Description |
|----------|-------------|
| `{{product_name}}` | Product title |
| `{{product_price}}` | Formatted price with currency |
| `{{product_quantity}}` | Selected quantity |
| `{{product_variation}}` | Variation details (if applicable) |
| `{{product_sku}}` | Product SKU code |
| `{{product_link}}` | Direct link to product |
| `{{cart_total}}` | Cart total amount (cart context) |
| `{{site_name}}` | Your WordPress site name |
| `{{customer_name}}` | Customer's display name or "Guest Customer" |

### Styling Options

- **Button Color**: Choose custom button background color
- **Hover Color**: Set button hover state color
- **Icon Size**: Adjust WhatsApp icon size
- **Custom CSS**: Add your own custom styles

## 💡 Usage Examples

### For Customers

1. **Product Page**: Click "Order via WhatsApp" button on any product
2. **Auto-Detection**: Plugin detects your device (mobile/desktop) and opens appropriate WhatsApp interface
3. **Pre-filled Message**: WhatsApp opens with your order details already filled
4. **Send & Order**: Simply send the message to complete your order

### Message Template Examples

**Product Order (Default)**:
```
Hello, I want to order {{product_name}}{{product_variation}} - {{product_price}} x {{product_quantity}}

Please confirm availability.
```

**Cart Order (Default)**:
```
New order from {{site_name}}
Customer: {{customer_name}}
Cart total: {{cart_total}}

Please process this order.
```

**Custom Professional Template**:
```
🛒 NEW ORDER REQUEST

Customer: {{customer_name}}
Product: {{product_name}}{{product_variation}}
Quantity: {{product_quantity}}
Price: {{product_price}}
Link: {{product_link}}

From: {{site_name}}

Please confirm availability and delivery time.
```

## 🔄 What's New in Version 1.2.0

### ✨ New Features

1. **Responsive Device Detection**
   - Desktop users: Opens `web.whatsapp.com` for WhatsApp Web
   - Mobile users: Opens WhatsApp app directly
   - Automatic detection using WordPress `wp_is_mobile()` function

2. **Guest User Support**
   - Non-logged-in users can now use WhatsApp buttons
   - Fixed nonce verification to allow `wp_ajax_nopriv` actions
   - Guest customers appear as "Guest Customer" in messages

3. **Blank Message Prevention**
   - Smart validation prevents empty messages
   - Fallback messages when template variables are missing
   - Ensures WhatsApp always opens with content

### 🛠️ Improvements

- Enhanced AJAX security for non-logged-in users
- Better customer name handling (logged-in, guest, or custom)
- Improved message template parser
- Optimized WhatsApp URL generation
- Documentation cleanup and reorganization

## 📱 Device Detection Logic

The plugin automatically detects the user's device and uses the optimal WhatsApp endpoint:

| Device Type | API Choice | Endpoint Used | Behavior |
|-------------|-----------|---------------|----------|
| Desktop | `api` | `web.whatsapp.com` | Opens WhatsApp Web |
| Mobile | `api` | `api.whatsapp.com` | Opens WhatsApp App |
| Desktop | `wa` | `wa.me` | Auto-redirects appropriately |
| Mobile | `wa` | `wa.me` | Auto-redirects appropriately |
| Any | `custom` | Your custom URL | Uses your configuration |

## 🔐 Security Features

- ✅ WordPress nonce verification for all AJAX requests
- ✅ Capability checks for admin functions
- ✅ Input sanitization and validation
- ✅ Output escaping for XSS prevention
- ✅ SQL injection protection via WordPress APIs
- ✅ Secure AJAX handlers for logged-in and guest users

## 🔌 Developer Hooks

### Available Filters

**Modify WhatsApp Endpoint URL**:
```php
add_filter( 'onlive_wa_order_endpoint', function( $endpoint, $message, $plugin ) {
    // Customize endpoint URL
    return $endpoint;
}, 10, 3 );
```

**Modify Generated Message**:
```php
add_filter( 'onlive_wa_order_message', function( $message, $context, $data, $plugin ) {
    // Add custom text or modify message
    $message .= "\n\nThank you for choosing our store!";
    return $message;
}, 10, 4 );
```

**Modify Button HTML**:
```php
add_filter( 'onlive_wa_order_button_html', function( $html, $product_id, $plugin ) {
    // Customize button markup
    return $html;
}, 10, 3 );
```

## 🆘 Troubleshooting

### Button Not Appearing

- ✓ Verify plugin is enabled in **WooCommerce → WhatsApp Order**
- ✓ Ensure WooCommerce is active and up-to-date
- ✓ Clear all caches (browser, WordPress, CDN)
- ✓ Check theme compatibility by switching to default theme
- ✓ Disable other plugins to check for conflicts

### WhatsApp Not Opening

- ✓ Verify phone number format: must include country code (e.g., `+1234567890`)
- ✓ Mobile: Ensure WhatsApp app is installed
- ✓ Desktop: Try different browsers
- ✓ Try different API choice in settings (`wa.me` is most compatible)
- ✓ Disable browser popup blockers

### Blank Message Box

- ✓ Now automatically prevented in v1.2.0
- ✓ Uses fallback messages if template data missing
- ✓ Check if template variables are correctly spelled
- ✓ Test with default template first

### Guest Users Can't Order

- ✓ Fixed in v1.2.0!
- ✓ Ensure you're using latest version
- ✓ Clear WordPress cache after update
- ✓ Test in incognito/private browsing mode

### Auto-Updates Not Working

- ✓ Verify GitHub Release is created (not just Git tag)
- ✓ Click "Check for Updates" button manually
- ✓ Check WordPress debug log for errors
- ✓ Verify site has internet connectivity

## 📊 Changelog

### Version 1.2.0 (2025-01-XX)

#### Added
- ✨ Responsive device detection (mobile/desktop)
- ✨ Guest user support for non-logged-in customers
- ✨ Blank message prevention with smart fallbacks
- ✨ Desktop-specific WhatsApp Web support

#### Fixed
- 🐛 AJAX nonce verification blocking guest users
- 🐛 Blank message box when customer name missing
- 🐛 WhatsApp link not opening correctly on desktop

#### Changed
- 📝 Documentation cleanup and reorganization
- 📝 Moved old .md files to docs/ folder
- 📝 Comprehensive new README with examples

### Version 1.1.0 (2024-12-31)

- ➕ Added "Check for Updates" button in admin
- 🔧 Improved update detection mechanism
- 🔧 Fixed version number consistency
- 📚 Enhanced documentation

### Version 1.0.0 (2024-12-01)

- 🎉 Initial release
- 🛍️ Product page integration
- 🛒 Cart page support
- 📝 Template system with variables
- ⚙️ Admin settings panel
- 🔄 GitHub auto-updater

## 🤝 Support

Need help? We're here for you!

- 📧 **Email**: support@onliveinfotech.com
- 🌐 **Website**: https://www.onlivetechnologies.com/
- 🐛 **GitHub Issues**: https://github.com/onliveserver/woo-whatsapp-order/issues
- 📚 **Documentation**: Check `/docs` folder for detailed guides

## 🌟 Contributing

We welcome contributions! Please:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This plugin is proprietary software developed by Onlive Technologies.

## 👏 Credits

**Developed by**: [Onlive Technologies](https://www.onlivetechnologies.com/)  
**Maintained by**: Onlive Development Team

## 🔒 Privacy Policy

This plugin respects your privacy:

- ❌ No user data collection or storage
- ❌ No external API calls (except GitHub for updates)
- ❌ No tracking or analytics
- ✅ All WhatsApp communications happen directly via WhatsApp platform
- ✅ Full GDPR compliance

---

**Made with ❤️ by [Onlive Technologies](https://www.onlivetechnologies.com/)**
