<?php
/**
 * Fired during plugin activation
 */
class ABMD_Activator {

    /**
     * Activation logic
     */
    public static function activate() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table for storing source product information
        $table_products = $wpdb->prefix . 'abmd_source_products';
        $sql_products = "CREATE TABLE IF NOT EXISTS $table_products (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            source_url varchar(500) NOT NULL,
            source_site varchar(100) NOT NULL,
            product_id bigint(20) DEFAULT NULL,
            source_product_id varchar(100) DEFAULT NULL,
            title text NOT NULL,
            description text,
            price decimal(10,2) NOT NULL,
            sale_price decimal(10,2) DEFAULT NULL,
            shipping_cost decimal(10,2) DEFAULT NULL,
            shipping_info text,
            image_urls text,
            artist varchar(200) DEFAULT NULL,
            condition_note varchar(100) DEFAULT NULL,
            stock_status varchar(50) DEFAULT 'instock',
            last_synced datetime DEFAULT CURRENT_TIMESTAMP,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY source_url (source_url(191)),
            KEY product_id (product_id)
        ) $charset_collate;";

        // Table for tracking dropship orders
        $table_orders = $wpdb->prefix . 'abmd_dropship_orders';
        $sql_orders = "CREATE TABLE IF NOT EXISTS $table_orders (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            wc_order_id bigint(20) NOT NULL,
            source_product_id bigint(20) NOT NULL,
            source_site varchar(100) NOT NULL,
            source_order_id varchar(100) DEFAULT NULL,
            status varchar(50) DEFAULT 'pending',
            customer_name varchar(200) NOT NULL,
            customer_email varchar(200) NOT NULL,
            shipping_address text NOT NULL,
            order_total decimal(10,2) NOT NULL,
            source_total decimal(10,2) NOT NULL,
            profit decimal(10,2) NOT NULL,
            error_message text,
            placed_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY wc_order_id (wc_order_id),
            KEY status (status)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_products);
        dbDelta($sql_orders);

        // Create default options
        add_option('abmd_markup_percentage', 20);
        add_option('abmd_auto_sync_enabled', true);
        add_option('abmd_auto_order_enabled', false); // Safety: disabled by default
        add_option('abmd_source_sites', json_encode([
            [
                'name' => 'Discogs Australia',
                'url' => 'https://www.discogs.com',
                'enabled' => true,
                'search_query' => 'australian+band'
            ]
        ]));

        // Schedule cron job for auto-sync
        if (!wp_next_scheduled('abmd_auto_sync_products')) {
            wp_schedule_event(time(), 'daily', 'abmd_auto_sync_products');
        }

        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
