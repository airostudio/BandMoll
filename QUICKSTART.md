# Quick Start Guide

## Installation (3 Steps)

### 1. Upload Plugin
```bash
cd wp-content/plugins/
git clone [your-repo-url] aussie-band-merch-dropship
cd aussie-band-merch-dropship
```

### 2. Install Dependencies
```bash
./setup.sh
```
Or manually:
```bash
composer install --no-dev --optimize-autoloader
```

### 3. Activate
- Go to WordPress Admin → Plugins
- Activate "Aussie Band Merch Dropship"

## First Use

### Configure Settings
1. Go to **Band Merch → Settings**
2. Set markup percentage (default: 20%)
3. Keep auto-ordering **disabled** for testing

### Import Your First Product
1. Go to **Band Merch → Import Products**
2. Enter a product URL:
   - Discogs: `https://www.discogs.com/sell/item/[id]`
   - eBay: `https://www.ebay.com.au/itm/[id]`
3. Click "Test Scraper" first
4. Then click "Import Products"

### Test Order Flow
1. Add imported product to cart
2. Complete checkout (test mode)
3. Check **Band Merch → Dropship Orders**
4. Manually place order on source site
5. Verify profit calculation is correct

## Common Tasks

### Import Multiple Products
Use search results pages:
```
Discogs: https://www.discogs.com/sell/list?q=australian+band&format=Vinyl
eBay: https://www.ebay.com.au/sch/i.html?_nkw=australian+vinyl
```

### Adjust Markup
- Settings → Markup Percentage
- Example: 20% = 1.20x multiplier
- Includes shipping costs in calculation

### Enable Auto-Ordering (Advanced)
⚠️ **Test thoroughly first!**
1. Configure API credentials in Settings
2. Test in staging environment
3. Enable "Automated Ordering"
4. Monitor first orders closely

## Troubleshooting

### Fatal Error
```bash
cd wp-content/plugins/aussie-band-merch-dropship/
composer install --no-dev
```

### "Database tables not found"
1. Deactivate plugin
2. Reactivate plugin
3. Tables will be created

### JavaScript Errors
- Clear browser cache
- Disable other plugins temporarily
- Check browser console for details

## File Structure
```
aussie-band-merch-dropship/
├── admin/                      # Admin interface
│   ├── class-admin.php        # Main admin class
│   ├── css/admin.css          # Styles
│   ├── js/admin.js            # JavaScript
│   └── views/                 # Page templates
├── includes/                   # Core functionality
│   ├── class-abmd-core.php    # Plugin core
│   ├── class-scraper.php      # Web scraper
│   ├── class-product-importer.php
│   ├── class-order-handler.php
│   └── class-shipping-parser.php
├── vendor/                     # Composer dependencies
├── aussie-band-merch-dropship.php  # Main plugin file
├── composer.json              # Dependencies config
├── README.md                  # Full documentation
├── INSTALL.md                 # Installation guide
├── TROUBLESHOOTING.md         # Common issues
└── setup.sh                   # Quick setup script
```

## Support

- **Documentation**: README.md
- **Installation Help**: INSTALL.md
- **Troubleshooting**: TROUBLESHOOTING.md
- **Issues**: GitHub Issues

## Safety Checklist

Before going live:
- [ ] Test in staging environment
- [ ] Import test products
- [ ] Place test order
- [ ] Verify profit calculations
- [ ] Test shipping cost parsing
- [ ] Keep auto-ordering disabled initially
- [ ] Set up backups
- [ ] Configure WooCommerce shipping
- [ ] Set up payment gateway

## Key Features

✅ Auto-import from Discogs & eBay Australia
✅ 20% markup (configurable)
✅ Intelligent shipping cost detection
✅ Automated dropship ordering (optional)
✅ Profit tracking dashboard
✅ WooCommerce integration
✅ Product image import
✅ Order management interface

## Tips

- Start with manual order fulfillment
- Test scraper before bulk imports
- Monitor first few orders closely
- Adjust markup based on your costs
- Use staging site for testing
- Back up database regularly
- Keep plugin updated

Enjoy! 🎸
