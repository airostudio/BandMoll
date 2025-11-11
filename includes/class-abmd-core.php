<?php
/**
 * Core plugin class
 */
class ABMD_Core {

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->plugin_name = 'aussie-band-merch-dropship';
        $this->version = ABMD_VERSION;
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        // Dependencies are autoloaded via Composer
    }

    private function define_admin_hooks() {
        $admin = new ABMD_Admin($this->plugin_name, $this->version);

        add_action('admin_enqueue_scripts', [$admin, 'enqueue_styles']);
        add_action('admin_enqueue_scripts', [$admin, 'enqueue_scripts']);
        add_action('admin_menu', [$admin, 'add_plugin_admin_menu']);
        add_action('admin_init', [$admin, 'register_settings']);
    }

    private function define_public_hooks() {
        $order_handler = new ABMD_Order_Handler();

        // Hook into WooCommerce order processing
        add_action('woocommerce_order_status_processing', [$order_handler, 'handle_new_order'], 10, 1);
        add_action('woocommerce_order_status_completed', [$order_handler, 'handle_completed_order'], 10, 1);

        // Cron job for auto-sync
        add_action('abmd_auto_sync_products', [$this, 'auto_sync_products']);

        // Ajax handlers
        add_action('wp_ajax_abmd_sync_products', [$this, 'ajax_sync_products']);
        add_action('wp_ajax_abmd_import_product', [$this, 'ajax_import_product']);
        add_action('wp_ajax_abmd_test_scraper', [$this, 'ajax_test_scraper']);
    }

    public function run() {
        // Plugin is now running with all hooks registered
    }

    /**
     * Auto sync products from configured sources
     */
    public function auto_sync_products() {
        if (!get_option('abmd_auto_sync_enabled', true)) {
            return;
        }

        $scraper = new ABMD_Scraper();
        $importer = new ABMD_Product_Importer();

        $source_sites = json_decode(get_option('abmd_source_sites', '[]'), true);

        foreach ($source_sites as $site) {
            if (!$site['enabled']) {
                continue;
            }

            try {
                $products = $scraper->scrape_site($site);
                foreach ($products as $product_data) {
                    $importer->import_product($product_data);
                }
            } catch (Exception $e) {
                error_log('ABMD Auto Sync Error: ' . $e->getMessage());
            }
        }
    }

    /**
     * AJAX handler for manual product sync
     */
    public function ajax_sync_products() {
        check_ajax_referer('abmd-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $site_url = sanitize_text_field($_POST['site_url'] ?? '');

        try {
            $scraper = new ABMD_Scraper();
            $importer = new ABMD_Product_Importer();

            $products = $scraper->scrape_url($site_url);
            $imported_count = 0;

            foreach ($products as $product_data) {
                if ($importer->import_product($product_data)) {
                    $imported_count++;
                }
            }

            wp_send_json_success([
                'message' => sprintf('Successfully imported %d products', $imported_count),
                'count' => $imported_count
            ]);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX handler for importing single product
     */
    public function ajax_import_product() {
        check_ajax_referer('abmd-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $product_url = sanitize_text_field($_POST['product_url'] ?? '');

        try {
            $scraper = new ABMD_Scraper();
            $importer = new ABMD_Product_Importer();

            $product_data = $scraper->scrape_single_product($product_url);
            $product_id = $importer->import_product($product_data);

            if ($product_id) {
                wp_send_json_success([
                    'message' => 'Product imported successfully',
                    'product_id' => $product_id
                ]);
            } else {
                wp_send_json_error(['message' => 'Failed to import product']);
            }
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX handler for testing scraper
     */
    public function ajax_test_scraper() {
        check_ajax_referer('abmd-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $test_url = sanitize_text_field($_POST['test_url'] ?? '');

        try {
            $scraper = new ABMD_Scraper();
            $result = $scraper->test_url($test_url);

            wp_send_json_success([
                'message' => 'Scraper test completed',
                'data' => $result
            ]);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
}
