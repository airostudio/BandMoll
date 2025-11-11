# Aussie Band Merch Dropship WordPress Plugin

A WordPress/WooCommerce plugin for automatically importing and dropshipping Australian band merchandise from second-hand record stores. Inspired by AliExpress dropshipping plugins but tailored for the Australian music merchandise market.

## Features

- **Automated Product Import**: Scrape products from Discogs, eBay Australia, and other second-hand record sites
- **20% Markup (Configurable)**: Automatically applies markup to source prices including shipping costs
- **Shipping Parser**: Intelligently extracts postage/shipping information from source sites
- **Automated Dropshipping**: When orders come in, automatically place orders on source sites with customer details
- **Profit Tracking**: Monitor your profit margins and order status
- **WooCommerce Integration**: Seamlessly integrates with WooCommerce
- **Product Management**: Track source products and link them to WooCommerce products
- **Order Management**: View and manage dropship orders in one place

## Requirements

- WordPress 5.8 or higher
- WooCommerce 5.0 or higher
- PHP 7.4 or higher
- Composer (for installing dependencies)

## Installation

### 1. Clone or Download

```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/yourusername/aussie-band-merch-dropship.git
cd aussie-band-merch-dropship
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Activate Plugin

Go to WordPress Admin → Plugins → Activate "Aussie Band Merch Dropship"

### 4. Configure Settings

Navigate to **Band Merch → Settings** and configure:

- Markup percentage (default: 20%)
- Auto-sync settings
- API credentials (optional, for automated ordering)

## Usage

### Import Products

1. Go to **Band Merch → Import Products**
2. Enter a product URL or search results page URL from supported sites
3. Click "Import Products"
4. Products will be automatically created in WooCommerce with markup applied

### Automated Dropshipping

When enabled, the plugin will:

1. Detect when a customer places an order for a dropship product
2. Calculate the source cost and your profit
3. Automatically place an order on the source site (if API credentials configured)
4. Forward the customer's shipping address
5. Track the order status

**⚠️ WARNING**: Test thoroughly before enabling automated ordering in production!

### Manual Dropshipping

If automated ordering is disabled (recommended for initial setup):

1. Orders will be logged in **Band Merch → Dropship Orders**
2. View pending orders and manually place them on source sites
3. Mark orders as "placed" once completed

## Supported Sites

### Discogs
- Marketplace listings
- Australian sellers
- Vinyl records and band merchandise
- **API Integration**: Requires Discogs API credentials for automated ordering

### eBay Australia
- Auction and Buy It Now listings
- Vinyl records and band merchandise
- **API Integration**: Requires eBay API credentials for automated ordering

### Generic Sites
- Basic scraping support for other sites
- May require customization

## Configuration

### Markup Calculation

The markup is applied to the **total source cost** (product price + shipping):

```
Source Price: $50
Shipping Cost: $10
Total Source Cost: $60
Markup: 20%
Sell Price: $60 × 1.20 = $72
```

### API Credentials

To enable automated ordering:

**Discogs API**:
1. Go to [Discogs Developer Settings](https://www.discogs.com/settings/developers)
2. Create an application
3. Copy Consumer Key and Consumer Secret
4. Enter in plugin settings

**eBay API**:
1. Go to [eBay Developer Portal](https://developer.ebay.com/my/keys)
2. Create an application
3. Copy App ID and Cert ID
4. Enter in plugin settings

## Database Tables

The plugin creates two custom tables:

### `wp_abmd_source_products`
Stores information about products scraped from source sites:
- Source URL, site, and product ID
- Title, description, artist
- Pricing and shipping information
- Link to WooCommerce product
- Last sync timestamp

### `wp_abmd_dropship_orders`
Tracks dropship orders:
- WooCommerce order ID
- Source product information
- Customer details
- Order status (pending, placed, failed, completed)
- Profit calculation

## Development

### File Structure

```
aussie-band-merch-dropship/
├── admin/
│   ├── class-admin.php          # Admin interface
│   ├── css/admin.css            # Admin styles
│   ├── js/admin.js              # Admin JavaScript
│   └── views/                   # Admin page templates
│       ├── dashboard.php
│       ├── import.php
│       ├── orders.php
│       ├── source-products.php
│       └── settings.php
├── includes/
│   ├── class-abmd-core.php      # Core plugin logic
│   ├── class-activator.php      # Activation logic
│   ├── class-deactivator.php    # Deactivation logic
│   ├── class-scraper.php        # Web scraper
│   ├── class-product-importer.php  # Product import logic
│   ├── class-order-handler.php  # Order automation
│   └── class-shipping-parser.php   # Shipping info parser
├── vendor/                       # Composer dependencies
├── aussie-band-merch-dropship.php  # Main plugin file
├── composer.json
└── README.md
```

### Adding New Sites

To add support for a new site:

1. Add scraping logic in `includes/class-scraper.php`:
   - Create a `scrape_yoursite()` method
   - Create a `parse_yoursite_product()` method

2. Add shipping parsing logic in `includes/class-shipping-parser.php`:
   - Create a `parse_yoursite_shipping()` method

3. Add API integration in `includes/class-order-handler.php`:
   - Create a `place_yoursite_order()` method

## Hooks and Filters

### Actions

```php
// Before product import
do_action('abmd_before_import_product', $product_data);

// After product import
do_action('abmd_after_import_product', $product_id, $product_data);

// Before placing dropship order
do_action('abmd_before_place_order', $dropship_order_id, $source_product, $wc_order);

// After placing dropship order
do_action('abmd_after_place_order', $dropship_order_id, $result);
```

### Filters

```php
// Modify markup percentage per product
$markup = apply_filters('abmd_product_markup', $markup_percentage, $product_data);

// Modify scraped product data before import
$product_data = apply_filters('abmd_scraped_product_data', $product_data, $source_url);

// Modify sell price calculation
$sell_price = apply_filters('abmd_sell_price', $sell_price, $source_price, $shipping_cost);
```

## Cron Jobs

The plugin schedules a daily cron job to auto-sync products:

```php
wp_schedule_event(time(), 'daily', 'abmd_auto_sync_products');
```

To manually trigger:
```bash
wp cron event run abmd_auto_sync_products
```

## Troubleshooting

### Products Not Importing
- Check if the source URL is from a supported site
- Test the scraper using "Test Scraper" button
- Check WordPress error logs for scraping errors

### Orders Not Auto-Placing
- Ensure API credentials are configured correctly
- Check that automated ordering is enabled in settings
- Review error messages in Dropship Orders page

### Images Not Importing
- Check WordPress media upload permissions
- Ensure `allow_url_fopen` is enabled in PHP
- Check for SSL certificate issues with source sites

## Security Considerations

- API credentials are stored in WordPress options (consider encrypting)
- Automated ordering should be thoroughly tested in sandbox/staging
- Rate limiting may be needed for high-volume scraping
- Consider using proxies for large-scale scraping

## Legal Considerations

- Respect source sites' Terms of Service
- Check robots.txt before scraping
- Be mindful of rate limits
- Ensure you have rights to resell products
- Comply with consumer protection laws
- Handle customer data according to privacy laws

## Roadmap

- [ ] Add more source sites (JB Hi-Fi, Sanity Records, etc.)
- [ ] Implement rate limiting for scraping
- [ ] Add proxy support
- [ ] Improve shipping calculation logic
- [ ] Add order tracking integration
- [ ] Create bulk import scheduler
- [ ] Add reporting and analytics
- [ ] Implement stock level syncing
- [ ] Add automatic price updates

## License

GPL v2 or later

## Support

For issues and feature requests, please use the GitHub issue tracker.

## Credits

Inspired by AliExpress dropshipping plugins and built for the Australian band merchandise market.

## Disclaimer

This plugin is provided as-is. Use at your own risk. Always test thoroughly in a staging environment before using in production. Automated ordering can place real orders and spend real money.
