# Installation Guide

## Quick Start

### 1. Prerequisites

Ensure you have:
- WordPress 5.8+ installed
- WooCommerce 5.0+ installed and activated
- PHP 7.4+ on your server
- Composer installed on your development machine
- SSH/FTP access to your WordPress installation

### 2. Install the Plugin

#### Option A: Via Git (Recommended for Development)

```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/yourusername/aussie-band-merch-dropship.git
cd aussie-band-merch-dropship
composer install
```

#### Option B: Manual Upload

1. Download the plugin ZIP from GitHub
2. Extract to your computer
3. Run `composer install` in the extracted folder
4. Upload the entire folder to `wp-content/plugins/` via FTP
5. Ensure file permissions are correct (755 for folders, 644 for files)

### 3. Activate the Plugin

1. Log in to WordPress Admin
2. Navigate to **Plugins → Installed Plugins**
3. Find "Aussie Band Merch Dropship"
4. Click **Activate**

### 4. Initial Configuration

After activation, the plugin will:
- Create database tables automatically
- Set up default options (20% markup)
- Schedule daily auto-sync cron job

Navigate to **Band Merch → Settings** to configure:

#### Basic Settings

- **Markup Percentage**: Set your desired profit margin (default: 20%)
- **Auto Sync**: Enable/disable daily product synchronization
- **Automated Ordering**: ⚠️ Keep disabled until fully tested

#### API Credentials (Optional)

For automated ordering, configure API credentials:

**Discogs API**:
1. Visit [https://www.discogs.com/settings/developers](https://www.discogs.com/settings/developers)
2. Click "Create an Application"
3. Fill in application details:
   - Name: Your Store Name
   - Description: Dropshipping integration
   - Website: Your website URL
4. Copy "Consumer Key" and "Consumer Secret"
5. Paste into plugin settings

**eBay API**:
1. Visit [https://developer.ebay.com/my/keys](https://developer.ebay.com/my/keys)
2. Request production keys
3. Copy "App ID" and "Cert ID"
4. Paste into plugin settings

### 5. Import Your First Products

1. Go to **Band Merch → Import Products**
2. Enter a product URL, for example:
   - Discogs: `https://www.discogs.com/sell/list?q=australian+band&format=Vinyl`
   - eBay: `https://www.ebay.com.au/sch/i.html?_nkw=australian+vinyl`
3. Click "Test Scraper" to verify it works
4. Click "Import Products" to import

### 6. Verify Products

1. Go to **WooCommerce → Products**
2. Check that products were imported correctly
3. Verify pricing includes markup
4. Check product images loaded properly

### 7. Test Order Flow (Important!)

Before going live:

1. Place a test order on your site
2. Check **Band Merch → Dropship Orders**
3. Verify order details are correct
4. **With auto-ordering disabled**: Manually place the order on source site
5. Mark order as complete in the plugin

### 8. Enable Auto-Ordering (Optional)

⚠️ **Only after thorough testing!**

1. Ensure API credentials are configured
2. Test in sandbox/staging environment first
3. Enable "Automated Ordering" in settings
4. Monitor first few orders closely

## Troubleshooting

### Plugin Won't Activate

- Check PHP version (must be 7.4+)
- Ensure WooCommerce is installed and activated
- Check WordPress error logs

### Composer Install Fails

```bash
# Try clearing cache
composer clear-cache
composer install

# Or use --no-dev for production
composer install --no-dev --optimize-autoloader
```

### Products Not Importing

- Test the scraper first with "Test Scraper" button
- Check source site is supported
- Verify source URL is correct format
- Check PHP error logs

### Images Not Loading

- Check WordPress upload permissions: `wp-content/uploads` should be writable
- Verify `allow_url_fopen` is enabled in PHP
- Test with a single product first

### Cron Job Not Running

```bash
# Manually trigger cron job via WP-CLI
wp cron event run abmd_auto_sync_products

# Or check scheduled events
wp cron event list
```

## File Permissions

Recommended permissions:
```bash
# Folders
find . -type d -exec chmod 755 {} \;

# Files
find . -type f -exec chmod 644 {} \;
```

## Server Requirements

Minimum requirements:
- PHP 7.4+
- MySQL 5.6+ or MariaDB 10.0+
- WordPress 5.8+
- WooCommerce 5.0+
- 64MB+ PHP memory limit (256MB recommended)
- `allow_url_fopen` enabled
- `curl` extension enabled

## Security Checklist

Before going live:

- [ ] Test all features in staging environment
- [ ] Configure API credentials securely
- [ ] Set up SSL certificate (HTTPS)
- [ ] Review automated ordering settings
- [ ] Set appropriate user permissions
- [ ] Configure backup system
- [ ] Review WordPress security best practices
- [ ] Test with small orders first

## Getting Help

If you encounter issues:

1. Check the [README.md](README.md) for detailed documentation
2. Review WordPress error logs: `wp-content/debug.log`
3. Enable WP_DEBUG in `wp-config.php` for troubleshooting
4. Submit issues on GitHub

## Next Steps

After installation:

1. Import some products
2. Test the order flow
3. Configure shipping settings in WooCommerce
4. Set up payment gateways
5. Customize product descriptions and categories
6. Set up email notifications
7. Monitor profit tracking in dashboard

Enjoy your automated dropshipping system! 🎸
