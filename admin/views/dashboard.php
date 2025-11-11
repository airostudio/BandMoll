<?php
// Dashboard view
if (!defined('ABSPATH')) exit;
?>

<div class="wrap">
    <h1>Aussie Band Merch Dropship - Dashboard</h1>

    <div class="abmd-dashboard">
        <div class="abmd-stats">
            <div class="abmd-stat-box">
                <h3>Total Products</h3>
                <div class="stat-number"><?php echo number_format($total_products); ?></div>
            </div>

            <div class="abmd-stat-box">
                <h3>Total Orders</h3>
                <div class="stat-number"><?php echo number_format($total_orders); ?></div>
            </div>

            <div class="abmd-stat-box abmd-warning">
                <h3>Pending Orders</h3>
                <div class="stat-number"><?php echo number_format($pending_orders); ?></div>
            </div>

            <div class="abmd-stat-box abmd-success">
                <h3>Total Profit</h3>
                <div class="stat-number">$<?php echo number_format($total_profit ?? 0, 2); ?></div>
            </div>
        </div>

        <div class="abmd-quick-actions">
            <h2>Quick Actions</h2>

            <div class="abmd-actions-grid">
                <a href="<?php echo admin_url('admin.php?page=aussie-band-merch-dropship-import'); ?>" class="button button-primary button-hero">
                    Import Products
                </a>

                <a href="<?php echo admin_url('admin.php?page=aussie-band-merch-dropship-orders'); ?>" class="button button-secondary button-hero">
                    View Orders
                </a>

                <a href="<?php echo admin_url('admin.php?page=aussie-band-merch-dropship-settings'); ?>" class="button button-secondary button-hero">
                    Settings
                </a>
            </div>
        </div>

        <div class="abmd-info">
            <h2>How It Works</h2>
            <ol>
                <li><strong>Scrape Products:</strong> Import products from second-hand record sites like Discogs and eBay Australia</li>
                <li><strong>Automatic Markup:</strong> Products are automatically marked up by <?php echo get_option('abmd_markup_percentage', 20); ?>%</li>
                <li><strong>Dropship Orders:</strong> When a customer orders, the plugin can automatically place the order with the source site</li>
                <li><strong>Track Profit:</strong> Monitor your profit margins and order status</li>
            </ol>

            <div class="notice notice-info">
                <p><strong>Note:</strong> Automated ordering requires API credentials for each source site. Configure these in the Settings page.</p>
            </div>

            <?php if ($pending_orders > 0): ?>
            <div class="notice notice-warning">
                <p><strong>Action Required:</strong> You have <?php echo $pending_orders; ?> pending dropship orders that need to be placed.</p>
                <p><a href="<?php echo admin_url('admin.php?page=aussie-band-merch-dropship-orders'); ?>" class="button button-primary">View Pending Orders</a></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
