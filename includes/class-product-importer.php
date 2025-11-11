<?php
/**
 * Product importer with markup calculation
 */
class ABMD_Product_Importer {

    private $markup_percentage;

    public function __construct() {
        $this->markup_percentage = floatval(get_option('abmd_markup_percentage', 20));
    }

    /**
     * Import product from scraped data
     */
    public function import_product($product_data) {
        global $wpdb;

        if (empty($product_data['title']) || empty($product_data['source_url'])) {
            return false;
        }

        // Check if product already exists
        $table = $wpdb->prefix . 'abmd_source_products';
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE source_url = %s",
            $product_data['source_url']
        ));

        // Calculate prices with markup
        $source_price = floatval($product_data['price'] ?? 0);
        $shipping_cost = floatval($product_data['shipping_cost'] ?? 0);
        $total_source_cost = $source_price + $shipping_cost;

        // Apply markup percentage (default 20%)
        $markup_multiplier = 1 + ($this->markup_percentage / 100);
        $sell_price = round($total_source_cost * $markup_multiplier, 2);

        // Create or update WooCommerce product
        if ($existing && $existing->product_id) {
            $product_id = $existing->product_id;
            $product = wc_get_product($product_id);

            if (!$product) {
                // Product was deleted, create new one
                $product_id = $this->create_wc_product($product_data, $sell_price);
            } else {
                // Update existing product
                $this->update_wc_product($product, $product_data, $sell_price);
            }
        } else {
            // Create new WooCommerce product
            $product_id = $this->create_wc_product($product_data, $sell_price);
        }

        if (!$product_id) {
            return false;
        }

        // Save to our tracking table
        $source_product_data = [
            'source_url' => $product_data['source_url'],
            'source_site' => $product_data['source_site'] ?? 'Unknown',
            'product_id' => $product_id,
            'source_product_id' => $product_data['source_product_id'] ?? null,
            'title' => $product_data['title'],
            'description' => $product_data['description'] ?? '',
            'price' => $source_price,
            'shipping_cost' => $shipping_cost,
            'shipping_info' => $product_data['shipping_info'] ?? '',
            'image_urls' => is_array($product_data['image_urls'] ?? null)
                ? json_encode($product_data['image_urls'])
                : null,
            'artist' => $product_data['artist'] ?? null,
            'condition_note' => $product_data['condition'] ?? 'Used',
            'stock_status' => $product_data['stock_status'] ?? 'instock',
            'last_synced' => current_time('mysql'),
        ];

        if ($existing) {
            $wpdb->update(
                $table,
                $source_product_data,
                ['id' => $existing->id]
            );
        } else {
            $source_product_data['created_at'] = current_time('mysql');
            $wpdb->insert($table, $source_product_data);
        }

        return $product_id;
    }

    /**
     * Create WooCommerce product
     */
    private function create_wc_product($product_data, $sell_price) {
        $product = new WC_Product_Simple();

        $product->set_name($product_data['title']);
        $product->set_status('publish');
        $product->set_catalog_visibility('visible');
        $product->set_description($product_data['description'] ?? '');
        $product->set_short_description($this->generate_short_description($product_data));
        $product->set_regular_price($sell_price);
        $product->set_manage_stock(false); // Dropship - we don't manage stock
        $product->set_stock_status($product_data['stock_status'] ?? 'instock');

        // Add categories
        $category_ids = $this->get_or_create_categories($product_data);
        if (!empty($category_ids)) {
            $product->set_category_ids($category_ids);
        }

        // Add meta data for dropshipping
        $product->add_meta_data('_abmd_source_url', $product_data['source_url'], true);
        $product->add_meta_data('_abmd_source_site', $product_data['source_site'] ?? 'Unknown', true);
        $product->add_meta_data('_abmd_source_price', $product_data['price'] ?? 0, true);
        $product->add_meta_data('_abmd_is_dropship', 'yes', true);

        if (!empty($product_data['artist'])) {
            $product->add_meta_data('_abmd_artist', $product_data['artist'], true);
        }

        if (!empty($product_data['condition'])) {
            $product->add_meta_data('_abmd_condition', $product_data['condition'], true);
        }

        $product_id = $product->save();

        // Import images
        if (!empty($product_data['image_urls']) && is_array($product_data['image_urls'])) {
            $this->import_images($product_id, $product_data['image_urls']);
        }

        return $product_id;
    }

    /**
     * Update WooCommerce product
     */
    private function update_wc_product($product, $product_data, $sell_price) {
        $product->set_name($product_data['title']);
        $product->set_description($product_data['description'] ?? '');
        $product->set_regular_price($sell_price);
        $product->set_stock_status($product_data['stock_status'] ?? 'instock');

        // Update meta data
        $product->update_meta_data('_abmd_source_price', $product_data['price'] ?? 0);

        if (!empty($product_data['artist'])) {
            $product->update_meta_data('_abmd_artist', $product_data['artist']);
        }

        if (!empty($product_data['condition'])) {
            $product->update_meta_data('_abmd_condition', $product_data['condition']);
        }

        $product->save();

        return $product->get_id();
    }

    /**
     * Generate short description
     */
    private function generate_short_description($product_data) {
        $parts = [];

        if (!empty($product_data['artist'])) {
            $parts[] = 'Artist: ' . $product_data['artist'];
        }

        if (!empty($product_data['condition'])) {
            $parts[] = 'Condition: ' . $product_data['condition'];
        }

        $parts[] = 'Authentic Australian band merchandise';
        $parts[] = 'Sourced from ' . ($product_data['source_site'] ?? 'trusted seller');

        if (!empty($product_data['shipping_info'])) {
            $parts[] = 'Shipping: ' . $product_data['shipping_info'];
        }

        return implode(' | ', $parts);
    }

    /**
     * Get or create product categories
     */
    private function get_or_create_categories($product_data) {
        $category_ids = [];

        // Main category: Australian Band Merchandise
        $main_cat = $this->get_or_create_category('Australian Band Merch');
        if ($main_cat) {
            $category_ids[] = $main_cat;
        }

        // Add "Vinyl Records" category
        $vinyl_cat = $this->get_or_create_category('Vinyl Records');
        if ($vinyl_cat) {
            $category_ids[] = $vinyl_cat;
        }

        // Add condition-based category
        if (!empty($product_data['condition'])) {
            $condition_cat = $this->get_or_create_category('Second Hand');
            if ($condition_cat) {
                $category_ids[] = $condition_cat;
            }
        }

        return $category_ids;
    }

    /**
     * Get or create category
     */
    private function get_or_create_category($category_name) {
        $term = term_exists($category_name, 'product_cat');

        if ($term !== 0 && $term !== null) {
            return $term['term_id'];
        }

        $term = wp_insert_term($category_name, 'product_cat');

        if (is_wp_error($term)) {
            return null;
        }

        return $term['term_id'];
    }

    /**
     * Import product images
     */
    private function import_images($product_id, $image_urls) {
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $gallery_ids = [];

        foreach ($image_urls as $index => $image_url) {
            if (empty($image_url)) {
                continue;
            }

            try {
                // Download image
                $tmp = download_url($image_url);

                if (is_wp_error($tmp)) {
                    continue;
                }

                $file_array = [
                    'name' => basename($image_url),
                    'tmp_name' => $tmp
                ];

                // Upload to media library
                $attachment_id = media_handle_sideload($file_array, $product_id);

                if (is_wp_error($attachment_id)) {
                    @unlink($file_array['tmp_name']);
                    continue;
                }

                // First image becomes featured image
                if ($index === 0) {
                    set_post_thumbnail($product_id, $attachment_id);
                } else {
                    $gallery_ids[] = $attachment_id;
                }

            } catch (Exception $e) {
                error_log('Image import error: ' . $e->getMessage());
            }
        }

        // Set gallery images
        if (!empty($gallery_ids)) {
            update_post_meta($product_id, '_product_image_gallery', implode(',', $gallery_ids));
        }
    }

    /**
     * Calculate markup for a price
     */
    public function calculate_markup($original_price, $shipping_cost = 0) {
        $total_cost = floatval($original_price) + floatval($shipping_cost);
        $markup_multiplier = 1 + ($this->markup_percentage / 100);
        return round($total_cost * $markup_multiplier, 2);
    }
}
