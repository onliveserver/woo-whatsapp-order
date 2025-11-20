# GitHub Setup Guide for Onlive WhatsApp Order Plugin

This plugin includes automatic update functionality from GitHub. Follow these steps to set it up:

## Step 1: Create a GitHub Repository

1. Go to https://github.com/new
2. Create a new repository with these settings:
   - **Repository name**: `onlive-whatsapp-order`
   - **Description**: Onlive WooCommerce WhatsApp Order Plugin
   - **Public** (required for updates to work)
   - Click "Create repository"

## Step 2: Push Plugin to GitHub

Navigate to your local plugin directory and run:

```bash
cd /path/to/wp-content/plugins/onlive-whatsapp-order

# Initialize git repository
git init

# Add all files
git add .

# Initial commit
git commit -m "Initial commit: Onlive WhatsApp Order Plugin v1.0.0"

# Add remote repository
git remote add origin https://github.com/YOUR_USERNAME/onlive-whatsapp-order.git

# Rename branch to main (if needed)
git branch -M main

# Push to GitHub
git push -u origin main
```

## Step 3: Create Release Tags

The plugin checks GitHub for releases. Create a release:

1. Go to your GitHub repository
2. Click "Releases" → "Create a new release"
3. **Tag version**: `v1.0.0` (must match your plugin version)
4. **Release title**: `Version 1.0.0`
5. **Description**: Add changelog details
6. Click "Publish release"

## Step 4: Configure for Updates

The GitHub updater is configured in:
- **File**: `includes/class-github-updater.php`
- **Default repository**: `onlive-whatsapp-order/onlive-whatsapp-order`

### To customize the repository:

Edit the updater initialization in `woo-whatsapp-order-pro.php` bootstrap method:

```php
// Change owner and repo if needed
new Onlive_WA_Order_GitHub_Updater( 
    __FILE__, 
    'YOUR_GITHUB_USERNAME',  // GitHub username
    'onlive-whatsapp-order'  // Repository name
);
```

## Step 5: How Updates Work

1. **Version Check**: Plugin checks GitHub every hour for new releases
2. **Release Detection**: Looks for the latest release with a tag like `v1.x.x`
3. **Update Prompt**: WordPress shows an update notification when newer version available
4. **One-Click Update**: Admin can click "Update" to download and install

## Step 6: Publishing Updates

When you want to release a new version:

1. Update `Version` header in `woo-whatsapp-order-pro.php`:
   ```php
   * Version:           1.1.0
   ```

2. Commit changes:
   ```bash
   git add .
   git commit -m "Release version 1.1.0"
   git push origin main
   ```

3. Create a new GitHub release:
   - Tag: `v1.1.0`
   - Title: `Version 1.1.0`
   - Include changelog
   - Publish

4. WordPress will detect the new version within 1 hour and show update notification

## Troubleshooting

### Updates not showing?
- Verify repository is **public**
- Check tag format: must be `v` + version number (e.g., `v1.0.0`)
- Clear plugin update transient: add to functions.php temporarily:
  ```php
  delete_transient( 'onlive_wa_order_github_version' );
  ```

### Wrong version showing?
- Check GitHub release tag matches plugin version
- Remove `v` prefix from version in PHP, GitHub handles it

### API rate limits?
- GitHub allows 60 requests/hour unauthenticated
- For higher limits, add GitHub token to updater (optional)

## Files Modified

- `woo-whatsapp-order-pro.php` - Added updater initialization
- `includes/class-github-updater.php` - GitHub update checker (new file)

## Support

For issues with the updater, check:
1. Repository is public
2. Release tag format is correct (`vX.X.X`)
3. Plugin version matches release tag
4. GitHub account has push access
