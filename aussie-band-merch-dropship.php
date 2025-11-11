<?php
/**
 * Plugin Name: Aussie Band Merch Dropship
 * Plugin URI: https://github.com/yourusername/aussie-band-merch-dropship
 * Description: Automatically import Australian band merchandise from second-hand record stores with automated dropshipping. Inspired by AliExpress dropshipping plugins.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: aussie-band-merch-dropship
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('ABMD_VERSION', '1.0.0');
define('ABMD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ABMD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ABMD_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Check if Composer dependencies are installed
 */
function abmd_check_dependencies() {
    $autoload_file = ABMD_PLUGIN_DIR . 'vendor/autoload.php';

    if (!file_exists($autoload_file)) {
        add_action('admin_notices', function() {
            ?>
            <div class="notice notice-error">
                <p><strong>Aussie Band Merch Dropship Error:</strong> Composer dependencies are missing.</p>
                <p>Please run <code>composer install</code> in the plugin directory: <code><?php echo esc_html(ABMD_PLUGIN_DIR); ?></code></p>
                <p>Or download the complete plugin package with dependencies included.</p>
            </div>
            <?php
        });
        return false;
    }

    require_once $autoload_file;
    return true;
}

// Check dependencies first
if (!abmd_check_dependencies()) {
    return;
}

// Include core classes
require_once ABMD_PLUGIN_DIR . 'includes/class-activator.php';
require_once ABMD_PLUGIN_DIR . 'includes/class-deactivator.php';
require_once ABMD_PLUGIN_DIR . 'includes/class-abmd-core.php';
require_once ABMD_PLUGIN_DIR . 'includes/class-scraper.php';
require_once ABMD_PLUGIN_DIR . 'includes/class-product-importer.php';
require_once ABMD_PLUGIN_DIR . 'includes/class-order-handler.php';
require_once ABMD_PLUGIN_DIR . 'includes/class-shipping-parser.php';
require_once ABMD_PLUGIN_DIR . 'admin/class-admin.php';

/**
 * Activation hook
 */
function activate_abmd() {
    ABMD_Activator::activate();
}
register_activation_hook(__FILE__, 'activate_abmd');

/**
 * Deactivation hook
 */
function deactivate_abmd() {
    ABMD_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'deactivate_abmd');

/**
 * Check if WooCommerce is active
 */
function abmd_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            ?>
            <div class="notice notice-error">
                <p><?php _e('Aussie Band Merch Dropship requires WooCommerce to be installed and active.', 'aussie-band-merch-dropship'); ?></p>
            </div>
            <?php
        });
        return false;
    }
    return true;
}

/**
 * Initialize the plugin
 */
function run_abmd() {
    if (!abmd_check_woocommerce()) {
        return;
    }

    $plugin = new ABMD_Core();
    $plugin->run();
}
add_action('plugins_loaded', 'run_abmd');
