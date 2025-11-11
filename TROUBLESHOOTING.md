# TROUBLESHOOTING GUIDE

## Common Issues and Solutions

### Fatal Error on Plugin Activation

**Symptom**: Plugin causes a fatal error when activated

**Cause**: Composer dependencies not installed

**Solution**:
```bash
cd wp-content/plugins/aussie-band-merch-dropship/
composer install --no-dev
```

Or use the setup script:
```bash
cd wp-content/plugins/aussie-band-merch-dropship/
./setup.sh
```

---

### "Composer dependencies are missing" Error Message

**Symptom**: Admin notice showing "Composer dependencies are missing"

**Cause**: The vendor/ directory doesn't exist or vendor/autoload.php is missing

**Solution**:
1. SSH into your server
2. Navigate to the plugin directory:
   ```bash
   cd /path/to/wordpress/wp-content/plugins/aussie-band-merch-dropship/
   ```
3. Run composer install:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

**If you don't have Composer on your server**:
1. Install Composer locally on your computer
2. Run `composer install --no-dev` locally
3. Upload the entire `vendor/` directory to your server via FTP
4. Make sure the directory structure is:
   ```
   aussie-band-merch-dropship/
   ├── vendor/
   │   ├── autoload.php
   │   ├── composer/
   │   ├── guzzlehttp/
   │   └── ...
   ├── includes/
   ├── admin/
   └── aussie-band-merch-dropship.php
   ```

---

### WooCommerce Required Error

**Symptom**: "Aussie Band Merch Dropship requires WooCommerce to be installed and active"

**Cause**: WooCommerce is not installed or not activated

**Solution**:
1. Install WooCommerce from WordPress.org
2. Activate WooCommerce
3. The dropship plugin will automatically start working

---

### Products Not Importing

**Symptom**: Import button doesn't work or returns errors

**Possible Causes & Solutions**:

1. **Invalid URL Format**
   - Make sure you're using a supported site (Discogs, eBay Australia)
   - Check the URL is complete and correct

2. **Connection Issues**
   - Verify your server can make outbound HTTP requests
   - Check if `allow_url_fopen` is enabled in PHP
   - Verify cURL is installed

3. **Site Structure Changed**
   - Source websites may update their HTML structure
   - Check error logs for parsing errors
   - May need to update scraper code

4. **Rate Limiting**
   - Source sites may block rapid requests
   - Wait a few minutes and try again
   - Consider adding delays between imports

---

### Images Not Importing

**Symptom**: Products import but images are missing

**Possible Causes & Solutions**:

1. **Permission Issues**
   - Check `wp-content/uploads/` is writable
   - Set permissions: `chmod 755 wp-content/uploads/`

2. **PHP Settings**
   - Ensure `allow_url_fopen` is enabled
   - Check `upload_max_filesize` in php.ini

3. **SSL Certificate Issues**
   - Some servers have SSL verification issues
   - Check error logs for SSL errors

4. **Image URLs Invalid**
   - Source site may use lazy loading
   - Images may require authentication
   - Check the source product page manually

---

### Orders Not Auto-Placing

**Symptom**: Orders remain in "pending" status

**Expected Behavior**: Auto-ordering is **disabled by default** for safety

**Solutions**:

1. **Manual Order Placement** (Recommended for testing):
   - Go to Band Merch → Dropship Orders
   - View pending orders
   - Manually place orders on source sites
   - Mark as complete in the plugin

2. **Enable Auto-Ordering** (Use with caution):
   - Configure API credentials in Settings
   - Test in staging environment first
   - Enable "Automated Ordering" in Settings
   - Monitor first few orders closely

---

### Cron Job Not Running

**Symptom**: Auto-sync doesn't run daily

**Solutions**:

1. **Check WordPress Cron**:
   ```bash
   wp cron event list
   ```

2. **Manually Trigger**:
   ```bash
   wp cron event run abmd_auto_sync_products
   ```

3. **Disable WP-Cron and Use System Cron** (Recommended for production):

   In `wp-config.php`:
   ```php
   define('DISABLE_WP_CRON', true);
   ```

   Add to system crontab:
   ```bash
   */15 * * * * cd /path/to/wordpress && wp cron event run --due-now
   ```

---

### Plugin Conflict

**Symptom**: Plugin doesn't load or causes errors with other plugins

**Debugging Steps**:

1. Disable all other plugins
2. Activate only WooCommerce and this plugin
3. Test if it works
4. Re-enable other plugins one by one to find conflict

**Common Conflicts**:
- Other dropshipping plugins
- Aggressive caching plugins
- Security plugins blocking HTTP requests

---

### Permissions Issues

**Symptom**: Various "permission denied" errors

**Solution**:
```bash
cd wp-content/plugins/aussie-band-merch-dropship/

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Make setup script executable
chmod +x setup.sh
```

---

### Database Tables Not Created

**Symptom**: Plugin activated but features don't work

**Solution**:

1. Deactivate the plugin
2. Reactivate the plugin (this triggers the activation hook)
3. Check if tables exist:
   ```sql
   SHOW TABLES LIKE 'wp_abmd_%';
   ```
   Should show:
   - `wp_abmd_source_products`
   - `wp_abmd_dropship_orders`

4. If tables still missing, check WordPress error logs

---

### PHP Version Issues

**Symptom**: "Your PHP version is too old" or similar errors

**Solution**:
- Plugin requires PHP 7.4 or higher
- Contact your hosting provider to upgrade PHP
- Recommended: PHP 8.0 or higher

---

### Memory Limit Issues

**Symptom**: "Allowed memory size exhausted" errors

**Solution**:

Increase PHP memory limit in `wp-config.php`:
```php
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');
```

Or in `.htaccess`:
```apache
php_value memory_limit 256M
```

---

## Debugging Tips

### Enable WordPress Debug Mode

In `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Check logs at: `wp-content/debug.log`

### Check PHP Error Log

Location varies by server:
- `/var/log/php-fpm/error.log`
- `/var/log/apache2/error.log`
- `/var/log/nginx/error.log`

### Test Scraper

Use the "Test Scraper" button on the Import page to diagnose scraping issues.

---

## Still Need Help?

1. Check the [README.md](README.md) for full documentation
2. Review the [INSTALL.md](INSTALL.md) for installation steps
3. Submit an issue on GitHub with:
   - WordPress version
   - PHP version
   - Error messages from debug.log
   - Steps to reproduce the problem

---

## Performance Optimization

### For High Volume Stores

1. **Use System Cron** instead of WP-Cron
2. **Add Caching** for scraped data
3. **Increase Memory Limit** to 512MB
4. **Use Object Caching** (Redis/Memcached)
5. **Optimize Database** regularly
6. **Add Rate Limiting** to scraper

### Recommended Server Specs

- **Minimum**: 1GB RAM, 1 CPU core
- **Recommended**: 2GB+ RAM, 2+ CPU cores
- **PHP**: 8.0 or higher
- **MySQL**: 5.7+ or MariaDB 10.3+
