# GitHub Setup Guide for Onlive WhatsApp Order Plugin

This plugin includes automatic update functionality from GitHub. Follow these steps to set it up:

## ✅ Current Status

- **GitHub Owner**: `onliveserver`
- **Repository**: `onlive-whatsapp-order`
- **Local Setup**: ✅ Complete (Git initialized, committed)
- **GitHub Repository**: ⏳ **ACTION NEEDED** - Must be created
- **Push Status**: Ready to push once repository exists

---

## 🚀 Quick Start (3 Steps)

### Step 1: Create GitHub Repository

**Go to: https://github.com/new**

Fill in:
- **Repository name**: `onlive-whatsapp-order`
- **Description**: `WooCommerce WhatsApp Order Plugin - Customizable buttons with auto-update support`
- **Visibility**: **Public** ✅ (required for updates)
- **Initialize**: Leave everything unchecked (empty repository)

**Click: Create repository**

### Step 2: Push Code to GitHub

```bash
cd /c/xampp/htdocs/wordpress/wp-content/plugins/onlive-whatsapp-order

# Push to GitHub
git push -u origin main
```

The system will prompt for GitHub authentication via browser.

### Step 3: Create Release

**Go to: https://github.com/onliveserver/onlive-whatsapp-order/releases/new**

Fill in:
- **Tag**: `v1.0.0`
- **Title**: `Version 1.0.0 - Initial Release`
- **Description**: Use template below

---

## 📋 Release Description Template

```
# Onlive WooCommerce WhatsApp Order Plugin v1.0.0

## 🎉 Initial Release

### Features
- ✅ Customizable WhatsApp Order buttons on product & cart pages
- ✅ Multiple button templates and positioning options
- ✅ Comprehensive admin settings interface
- ✅ Custom message template builder with live preview
- ✅ GitHub-based auto-update system (hourly checks)
- ✅ International WhatsApp numbers (+91, +44, +447911123456, etc.)
- ✅ Color and size customization
- ✅ Support for wa.me and custom WhatsApp API endpoints
- ✅ Per-product override via metabox
- ✅ Translation-ready with i18n support

### Setup Instructions

1. **Install & Activate**
   - Upload plugin to WordPress
   - Activate from Plugins menu

2. **Configure Settings**
   - Go to **WhatsApp Order Pro** menu
   - Enter your WhatsApp number with country code (e.g., +447911123456)
   - Customize colors and size
   - Configure message template
   - Enable button positions (product/cart)

3. **Test**
   - Visit a product page
   - Check cart page
   - Verify WhatsApp button appears

### Admin Tabs

| Tab | Settings |
|-----|----------|
| **General** | Enable plugin, WhatsApp number, button positions |
| **Button** | Labels, colors (background/text), size |
| **Template** | Message templates with variable support |
| **API** | Choose wa.me or custom gateway |
| **Design** | Plugin CSS toggle, custom CSS |

### Available Variables in Templates

- `{{product_name}}` - Product title
- `{{product_price}}` - Product price
- `{{product_quantity}}` - Quantity selected
- `{{product_variation}}` - Selected variations
- `{{cart_total}}` - Cart subtotal
- `{{site_name}}` - WordPress site name
- `{{customer_name}}` - Current user name
- `{{current_date}}` - Today's date

### Requirements

- WordPress 6.0+
- WooCommerce 3.0+
- PHP 7.4+
- Internet connection (for updates check)

### Automatic Updates

The plugin checks GitHub every hour for updates:
1. Edit version in `woo-whatsapp-order-pro.php`
2. Create new GitHub release with tag (e.g., `v1.1.0`)
3. WordPress shows "Update available" notification
4. Click Update to install

### Support

- **Website**: https://www.onlivetechnologies.com/
- **Email**: support@onliveinfotech.com
- **GitHub**: https://github.com/onliveserver/onlive-whatsapp-order

### License

[Add your license here]
```

---

## 📖 Detailed Setup Guide

### Creating the GitHub Repository

1. **Go to https://github.com/new**
2. **Repository Settings**:
   - Name: `onlive-whatsapp-order`
   - Visibility: Public
   - Don't initialize (leave empty)
3. **Create**

### Pushing Code

Once repository is created:

```bash
cd /c/xampp/htdocs/wordpress/wp-content/plugins/onlive-whatsapp-order

# Verify everything is ready
git status
git log --oneline -1

# Push to GitHub
git push -u origin main

# Verify
git branch -a
```

### Creating Releases

**Release Tags Format**: `v1.0.0`, `v1.1.0`, `v2.0.0`

The updater looks for the highest version tag and checks it hourly.

### Publishing Updates

To release a new version:

1. **Update plugin version** in `woo-whatsapp-order-pro.php`:
   ```php
   * Version:           1.1.0
   ```

2. **Commit & push**:
   ```bash
   git add .
   git commit -m "Release version 1.1.0 - New features and fixes"
   git push origin main
   ```

3. **Create GitHub Release**:
   - Tag: `v1.1.0`
   - Title: `Version 1.1.0`
   - Add changelog

4. **Users will see "Update Available"** in WordPress admin within 1 hour

---

## ⚙️ Technical Details

### GitHub Updater Configuration

**File**: `includes/class-github-updater.php`

Current settings:
```php
private $github_owner = 'onliveserver';
private $github_repo = 'onlive-whatsapp-order';
```

The updater:
- Checks every hour (uses transients)
- Queries GitHub API for latest release
- Compares versions
- Shows WordPress update notification
- Handles one-click updates

### Initialization

**File**: `woo-whatsapp-order-pro.php` (bootstrap method)

```php
// GitHub updater - enabled automatically
new Onlive_WA_Order_GitHub_Updater( __FILE__ );
```

---

## 🔧 Troubleshooting

### "Repository not found" error
- Verify repository exists: https://github.com/onliveserver/onlive-whatsapp-order
- Check it's public (not private)
- Wait a few seconds if just created

### Updates not showing
- Repository must be **public**
- Release tag must be format: `v1.x.x`
- Clear plugin transient: `delete_transient( 'onlive_wa_order_github_version' );`

### Wrong version showing
- Check release tag matches plugin version
- GitHub tag should be `v1.0.0` if plugin version is `1.0.0`

### Authentication fails
- Use Personal Access Token from: https://github.com/settings/tokens
- Or use Git Credential Manager (Windows)

---

## 📝 Checklist

- [ ] Repository created on GitHub
- [ ] Code pushed to main branch
- [ ] v1.0.0 release created
- [ ] Plugin installed in WordPress
- [ ] WhatsApp number configured
- [ ] Button appears on product page
- [ ] WhatsApp link works correctly
- [ ] Settings save properly
- [ ] All tabs working

---

## 📞 Support

**Organization**: Onlive Technologies
**Website**: https://www.onlivetechnologies.com/
**Email**: support@onliveinfotech.com
**GitHub**: https://github.com/onliveserver/onlive-whatsapp-order
