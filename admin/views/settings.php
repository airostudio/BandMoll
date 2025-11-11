<?php
// Settings view
if (!defined('ABSPATH')) exit;

// Save settings
if (isset($_POST['abmd_save_settings'])) {
    check_admin_referer('abmd-settings');

    update_option('abmd_markup_percentage', floatval($_POST['markup_percentage'] ?? 20));
    update_option('abmd_auto_sync_enabled', isset($_POST['auto_sync_enabled']) ? 1 : 0);
    update_option('abmd_auto_order_enabled', isset($_POST['auto_order_enabled']) ? 1 : 0);
    update_option('abmd_discogs_api_key', sanitize_text_field($_POST['discogs_api_key'] ?? ''));
    update_option('abmd_discogs_api_secret', sanitize_text_field($_POST['discogs_api_secret'] ?? ''));
    update_option('abmd_ebay_api_key', sanitize_text_field($_POST['ebay_api_key'] ?? ''));
    update_option('abmd_ebay_api_secret', sanitize_text_field($_POST['ebay_api_secret'] ?? ''));

    echo '<div class="notice notice-success"><p>Settings saved successfully.</p></div>';
}

$markup_percentage = get_option('abmd_markup_percentage', 20);
$auto_sync_enabled = get_option('abmd_auto_sync_enabled', true);
$auto_order_enabled = get_option('abmd_auto_order_enabled', false);
$discogs_api_key = get_option('abmd_discogs_api_key', '');
$discogs_api_secret = get_option('abmd_discogs_api_secret', '');
$ebay_api_key = get_option('abmd_ebay_api_key', '');
$ebay_api_secret = get_option('abmd_ebay_api_secret', '');
?>

<div class="wrap">
    <h1>Settings</h1>

    <form method="post" action="">
        <?php wp_nonce_field('abmd-settings'); ?>

        <h2>General Settings</h2>
        <table class="form-table">
            <tr>
                <th><label for="markup_percentage">Markup Percentage:</label></th>
                <td>
                    <input type="number" id="markup_percentage" name="markup_percentage"
                           value="<?php echo esc_attr($markup_percentage); ?>"
                           min="0" max="1000" step="0.1" class="small-text" />
                    <span>%</span>
                    <p class="description">
                        Products will be marked up by this percentage. Default is 20%.
                        <br>Example: Source price $50 + shipping $10 = $60 × 1.20 = $72 sell price
                    </p>
                </td>
            </tr>

            <tr>
                <th><label for="auto_sync_enabled">Auto Sync Products:</label></th>
                <td>
                    <input type="checkbox" id="auto_sync_enabled" name="auto_sync_enabled"
                           value="1" <?php checked($auto_sync_enabled, 1); ?> />
                    <p class="description">Automatically sync products from configured sources daily</p>
                </td>
            </tr>

            <tr>
                <th><label for="auto_order_enabled">Automated Ordering:</label></th>
                <td>
                    <input type="checkbox" id="auto_order_enabled" name="auto_order_enabled"
                           value="1" <?php checked($auto_order_enabled, 1); ?> />
                    <span style="color: red; font-weight: bold;">⚠️ Use with caution!</span>
                    <p class="description">
                        <strong>WARNING:</strong> When enabled, orders will be placed automatically on source sites.
                        Requires API credentials configured below. Test thoroughly before enabling.
                    </p>
                </td>
            </tr>
        </table>

        <h2>API Credentials</h2>
        <p>To enable automated ordering, you need to configure API credentials for each source site.</p>

        <h3>Discogs API</h3>
        <table class="form-table">
            <tr>
                <th><label for="discogs_api_key">Consumer Key:</label></th>
                <td>
                    <input type="text" id="discogs_api_key" name="discogs_api_key"
                           value="<?php echo esc_attr($discogs_api_key); ?>" class="regular-text" />
                    <p class="description">
                        Get API credentials at: <a href="https://www.discogs.com/settings/developers" target="_blank">Discogs Developer Settings</a>
                    </p>
                </td>
            </tr>
            <tr>
                <th><label for="discogs_api_secret">Consumer Secret:</label></th>
                <td>
                    <input type="password" id="discogs_api_secret" name="discogs_api_secret"
                           value="<?php echo esc_attr($discogs_api_secret); ?>" class="regular-text" />
                </td>
            </tr>
        </table>

        <h3>eBay API</h3>
        <table class="form-table">
            <tr>
                <th><label for="ebay_api_key">App ID:</label></th>
                <td>
                    <input type="text" id="ebay_api_key" name="ebay_api_key"
                           value="<?php echo esc_attr($ebay_api_key); ?>" class="regular-text" />
                    <p class="description">
                        Get API credentials at: <a href="https://developer.ebay.com/my/keys" target="_blank">eBay Developer Portal</a>
                    </p>
                </td>
            </tr>
            <tr>
                <th><label for="ebay_api_secret">Cert ID:</label></th>
                <td>
                    <input type="password" id="ebay_api_secret" name="ebay_api_secret"
                           value="<?php echo esc_attr($ebay_api_secret); ?>" class="regular-text" />
                </td>
            </tr>
        </table>

        <h2>Source Sites Configuration</h2>
        <p>Configure which sites to scrape for products. (Coming soon)</p>

        <p class="submit">
            <button type="submit" name="abmd_save_settings" class="button button-primary">
                Save Settings
            </button>
        </p>
    </form>
</div>
