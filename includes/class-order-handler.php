<?php
use GuzzleHttp\Client;

/**
 * Automated dropship order handler
 */
class ABMD_Order_Handler {

    private $client;
    private $auto_order_enabled;

    public function __construct() {
        $this->auto_order_enabled = get_option('abmd_auto_order_enabled', false);

        $this->client = new Client([
            'timeout'  => 30,
            'verify' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);
    }

    /**
     * Handle new WooCommerce order
     */
    public function handle_new_order($order_id) {
        global $wpdb;

        $order = wc_get_order($order_id);

        if (!$order) {
            return;
        }

        // Get all items in the order
        $items = $order->get_items();

        foreach ($items as $item_id => $item) {
            $product_id = $item->get_product_id();
            $product = wc_get_product($product_id);

            if (!$product) {
                continue;
            }

            // Check if this is a dropship product
            $is_dropship = $product->get_meta('_abmd_is_dropship');

            if ($is_dropship !== 'yes') {
                continue;
            }

            // Get source product information
            $table = $wpdb->prefix . 'abmd_source_products';
            $source_product = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE product_id = %d",
                $product_id
            ));

            if (!$source_product) {
                $this->log_error($order_id, $product_id, 'Source product not found in database');
                continue;
            }

            // Calculate totals
            $order_total = floatval($item->get_total());
            $source_total = floatval($source_product->price) + floatval($source_product->shipping_cost);
            $profit = $order_total - $source_total;

            // Get customer shipping address
            $shipping_address = $this->format_shipping_address($order);

            // Create dropship order record
            $dropship_order_data = [
                'wc_order_id' => $order_id,
                'source_product_id' => $source_product->id,
                'source_site' => $source_product->source_site,
                'status' => 'pending',
                'customer_name' => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
                'customer_email' => $order->get_billing_email(),
                'shipping_address' => $shipping_address,
                'order_total' => $order_total,
                'source_total' => $source_total,
                'profit' => $profit,
                'created_at' => current_time('mysql'),
            ];

            $table_orders = $wpdb->prefix . 'abmd_dropship_orders';
            $wpdb->insert($table_orders, $dropship_order_data);
            $dropship_order_id = $wpdb->insert_id;

            // Add order note
            $order->add_order_note(
                sprintf(
                    'Dropship order created for %s from %s. Profit: $%s',
                    $product->get_name(),
                    $source_product->source_site,
                    number_format($profit, 2)
                )
            );

            // If auto-ordering is enabled, place the order automatically
            if ($this->auto_order_enabled) {
                $this->place_source_order($dropship_order_id, $source_product, $order);
            } else {
                // Add order note that manual ordering is required
                $order->add_order_note(
                    sprintf(
                        'MANUAL ACTION REQUIRED: Place order at %s with customer details.',
                        $source_product->source_url
                    ),
                    false,
                    true // Customer visible
                );
            }
        }
    }

    /**
     * Handle completed order
     */
    public function handle_completed_order($order_id) {
        // Additional logic when order is marked as completed
        // Could trigger confirmation emails, update tracking, etc.
    }

    /**
     * Place order on source site (automated dropshipping)
     */
    private function place_source_order($dropship_order_id, $source_product, $wc_order) {
        global $wpdb;

        $table_orders = $wpdb->prefix . 'abmd_dropship_orders';

        try {
            // Determine site type and use appropriate ordering method
            $site = $source_product->source_site;
            $source_url = $source_product->source_url;

            // NOTE: Automated ordering requires API access or form submission
            // This is a template that needs to be customized per site

            if (strpos($site, 'Discogs') !== false) {
                $result = $this->place_discogs_order($source_product, $wc_order);
            } elseif (strpos($site, 'eBay') !== false) {
                $result = $this->place_ebay_order($source_product, $wc_order);
            } else {
                throw new Exception('Automated ordering not supported for ' . $site);
            }

            if ($result['success']) {
                // Update dropship order status
                $wpdb->update(
                    $table_orders,
                    [
                        'status' => 'placed',
                        'source_order_id' => $result['order_id'] ?? null,
                        'placed_at' => current_time('mysql'),
                    ],
                    ['id' => $dropship_order_id]
                );

                // Add success note to WooCommerce order
                $wc_order->add_order_note(
                    sprintf(
                        'Dropship order placed successfully on %s. Order ID: %s',
                        $site,
                        $result['order_id'] ?? 'N/A'
                    )
                );
            } else {
                throw new Exception($result['error'] ?? 'Unknown error');
            }

        } catch (Exception $e) {
            // Log error
            $wpdb->update(
                $table_orders,
                [
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ],
                ['id' => $dropship_order_id]
            );

            // Add error note
            $wc_order->add_order_note(
                sprintf(
                    'FAILED to place dropship order: %s. Manual action required.',
                    $e->getMessage()
                ),
                false,
                true
            );

            $this->log_error($wc_order->get_id(), $source_product->product_id, $e->getMessage());
        }
    }

    /**
     * Place order on Discogs (requires Discogs API access)
     */
    private function place_discogs_order($source_product, $wc_order) {
        // NOTE: This requires Discogs API credentials
        // You would need to register your app with Discogs and get OAuth tokens

        $api_key = get_option('abmd_discogs_api_key');
        $api_secret = get_option('abmd_discogs_api_secret');

        if (empty($api_key) || empty($api_secret)) {
            return [
                'success' => false,
                'error' => 'Discogs API credentials not configured'
            ];
        }

        // Discogs API implementation would go here
        // For now, return a placeholder

        return [
            'success' => false,
            'error' => 'Discogs automated ordering requires API implementation. Please configure API credentials in settings and implement the API integration.'
        ];
    }

    /**
     * Place order on eBay (requires eBay API access)
     */
    private function place_ebay_order($source_product, $wc_order) {
        // NOTE: This requires eBay API credentials
        // You would need to register your app with eBay and get OAuth tokens

        $api_key = get_option('abmd_ebay_api_key');
        $api_secret = get_option('abmd_ebay_api_secret');

        if (empty($api_key) || empty($api_secret)) {
            return [
                'success' => false,
                'error' => 'eBay API credentials not configured'
            ];
        }

        // eBay API implementation would go here
        // For now, return a placeholder

        return [
            'success' => false,
            'error' => 'eBay automated ordering requires API implementation. Please configure API credentials in settings and implement the API integration.'
        ];
    }

    /**
     * Format shipping address for source order
     */
    private function format_shipping_address($order) {
        return json_encode([
            'first_name' => $order->get_shipping_first_name(),
            'last_name' => $order->get_shipping_last_name(),
            'company' => $order->get_shipping_company(),
            'address_1' => $order->get_shipping_address_1(),
            'address_2' => $order->get_shipping_address_2(),
            'city' => $order->get_shipping_city(),
            'state' => $order->get_shipping_state(),
            'postcode' => $order->get_shipping_postcode(),
            'country' => $order->get_shipping_country(),
        ]);
    }

    /**
     * Log error to database
     */
    private function log_error($order_id, $product_id, $message) {
        error_log(sprintf(
            'ABMD Order Handler Error [Order: %d, Product: %d]: %s',
            $order_id,
            $product_id,
            $message
        ));
    }

    /**
     * Get dropship orders for a WooCommerce order
     */
    public function get_dropship_orders($wc_order_id) {
        global $wpdb;

        $table = $wpdb->prefix . 'abmd_dropship_orders';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE wc_order_id = %d",
            $wc_order_id
        ));
    }

    /**
     * Manually mark dropship order as placed
     */
    public function mark_order_placed($dropship_order_id, $source_order_id = null) {
        global $wpdb;

        $table = $wpdb->prefix . 'abmd_dropship_orders';

        return $wpdb->update(
            $table,
            [
                'status' => 'placed',
                'source_order_id' => $source_order_id,
                'placed_at' => current_time('mysql'),
            ],
            ['id' => $dropship_order_id]
        );
    }
}
