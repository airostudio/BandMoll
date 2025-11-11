<?php
/**
 * Admin interface for the plugin
 */
class ABMD_Admin {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Enqueue admin styles
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            ABMD_PLUGIN_URL . 'admin/css/admin.css',
            [],
            $this->version,
            'all'
        );
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            ABMD_PLUGIN_URL . 'admin/js/admin.js',
            ['jquery'],
            $this->version,
            false
        );

        wp_localize_script($this->plugin_name, 'abmdAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('abmd-admin-nonce'),
        ]);
    }

    /**
     * Add plugin admin menu
     */
    public function add_plugin_admin_menu() {
        add_menu_page(
            'Aussie Band Merch Dropship',
            'Band Merch',
            'manage_options',
            $this->plugin_name,
            [$this, 'display_dashboard'],
            'dashicons-album',
            56
        );

        add_submenu_page(
            $this->plugin_name,
            'Dashboard',
            'Dashboard',
            'manage_options',
            $this->plugin_name,
            [$this, 'display_dashboard']
        );

        add_submenu_page(
            $this->plugin_name,
            'Import Products',
            'Import Products',
            'manage_options',
            $this->plugin_name . '-import',
            [$this, 'display_import_page']
        );

        add_submenu_page(
            $this->plugin_name,
            'Dropship Orders',
            'Dropship Orders',
            'manage_options',
            $this->plugin_name . '-orders',
            [$this, 'display_orders_page']
        );

        add_submenu_page(
            $this->plugin_name,
            'Source Products',
            'Source Products',
            'manage_options',
            $this->plugin_name . '-source-products',
            [$this, 'display_source_products']
        );

        add_submenu_page(
            $this->plugin_name,
            'Settings',
            'Settings',
            'manage_options',
            $this->plugin_name . '-settings',
            [$this, 'display_settings_page']
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('abmd_settings', 'abmd_markup_percentage');
        register_setting('abmd_settings', 'abmd_auto_sync_enabled');
        register_setting('abmd_settings', 'abmd_auto_order_enabled');
        register_setting('abmd_settings', 'abmd_source_sites');
        register_setting('abmd_settings', 'abmd_discogs_api_key');
        register_setting('abmd_settings', 'abmd_discogs_api_secret');
        register_setting('abmd_settings', 'abmd_ebay_api_key');
        register_setting('abmd_settings', 'abmd_ebay_api_secret');
    }

    /**
     * Display dashboard page
     */
    public function display_dashboard() {
        global $wpdb;

        $table_products = $wpdb->prefix . 'abmd_source_products';
        $table_orders = $wpdb->prefix . 'abmd_dropship_orders';

        $total_products = $wpdb->get_var("SELECT COUNT(*) FROM $table_products");
        $total_orders = $wpdb->get_var("SELECT COUNT(*) FROM $table_orders");
        $pending_orders = $wpdb->get_var("SELECT COUNT(*) FROM $table_orders WHERE status = 'pending'");
        $total_profit = $wpdb->get_var("SELECT SUM(profit) FROM $table_orders WHERE status IN ('placed', 'completed')");

        include ABMD_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    /**
     * Display import page
     */
    public function display_import_page() {
        include ABMD_PLUGIN_DIR . 'admin/views/import.php';
    }

    /**
     * Display orders page
     */
    public function display_orders_page() {
        global $wpdb;

        $table = $wpdb->prefix . 'abmd_dropship_orders';
        $orders = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC LIMIT 100");

        include ABMD_PLUGIN_DIR . 'admin/views/orders.php';
    }

    /**
     * Display source products page
     */
    public function display_source_products() {
        global $wpdb;

        $table = $wpdb->prefix . 'abmd_source_products';
        $products = $wpdb->get_results("SELECT * FROM $table ORDER BY last_synced DESC LIMIT 100");

        include ABMD_PLUGIN_DIR . 'admin/views/source-products.php';
    }

    /**
     * Display settings page
     */
    public function display_settings_page() {
        include ABMD_PLUGIN_DIR . 'admin/views/settings.php';
    }
}
