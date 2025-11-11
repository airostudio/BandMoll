<?php
/**
 * Shipping/Postage information parser
 */
class ABMD_Shipping_Parser {

    /**
     * Extract shipping information from HTML content
     */
    public function extract_shipping_info($html_content) {
        $shipping_info = [
            'cost' => 0,
            'details' => '',
            'methods' => [],
        ];

        // Common shipping keywords
        $keywords = [
            'postage', 'shipping', 'delivery', 'freight',
            'ship', 'post', 'mail', 'courier'
        ];

        // Convert to lowercase for easier matching
        $content_lower = strtolower($html_content);

        // Try to find shipping cost
        $shipping_info['cost'] = $this->extract_shipping_cost($content_lower);

        // Extract shipping details
        $shipping_info['details'] = $this->extract_shipping_details($html_content, $content_lower);

        // Extract shipping methods
        $shipping_info['methods'] = $this->extract_shipping_methods($content_lower);

        return $shipping_info;
    }

    /**
     * Extract shipping cost from content
     */
    private function extract_shipping_cost($content) {
        // Patterns to match shipping costs
        $patterns = [
            // "$10.00 shipping" or "shipping $10.00"
            '/(?:postage|shipping|delivery|freight)[:\s]+\$?([\d,]+\.?\d{0,2})/',
            '/\$?([\d,]+\.?\d{0,2})\s+(?:postage|shipping|delivery|freight)/',

            // "Ships for $10.00"
            '/ships?\s+(?:for|at|:)\s+\$?([\d,]+\.?\d{0,2})/',

            // "Free shipping" or "FREE postage"
            '/(free|no charge|$0)\s+(?:postage|shipping|delivery)/',

            // "+ $10.00 postage"
            '/\+\s*\$?([\d,]+\.?\d{0,2})\s+(?:postage|shipping|delivery)/',

            // Australian context: "Au $10.00 post"
            '/au[:\s]+\$?([\d,]+\.?\d{0,2})\s+(?:post|ship|mail)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                if (isset($matches[1])) {
                    $cost_str = strtolower($matches[1]);

                    // Check for free shipping
                    if (in_array($cost_str, ['free', 'no charge', '0', '$0'])) {
                        return 0;
                    }

                    // Convert to float
                    $cost = floatval(str_replace(',', '', $matches[1]));
                    if ($cost > 0 && $cost < 1000) { // Sanity check
                        return $cost;
                    }
                }
            }
        }

        return 0;
    }

    /**
     * Extract shipping details/description
     */
    private function extract_shipping_details($html, $content_lower) {
        $details = [];

        // Check for free shipping
        if (preg_match('/free\s+(?:postage|shipping|delivery)/i', $content_lower)) {
            $details[] = 'Free shipping';
        }

        // Check for express/priority shipping
        if (preg_match('/(?:express|priority|fast|rapid)\s+(?:post|shipping|delivery)/i', $content_lower)) {
            $details[] = 'Express shipping available';
        }

        // Check for standard shipping
        if (preg_match('/standard\s+(?:post|shipping|delivery)/i', $content_lower)) {
            $details[] = 'Standard shipping';
        }

        // Check for tracking
        if (preg_match('/(?:tracked|tracking|track(?:able)?)\s+(?:post|shipping|delivery)/i', $content_lower)) {
            $details[] = 'Tracked shipping';
        }

        // Check for Australia Post
        if (preg_match('/australia\s+post|auspost/i', $content_lower)) {
            $details[] = 'Via Australia Post';
        }

        // Check for estimated delivery
        if (preg_match('/(\d+[-\s]to[-\s]\d+|\d+)\s+(?:business\s+)?days?\s+delivery/i', $content_lower, $matches)) {
            $details[] = 'Delivery: ' . $matches[0];
        }

        // Check for location restrictions
        if (preg_match('/(?:ships?\s+(?:to|from))\s+(australia|au|sydney|melbourne|brisbane)/i', $content_lower, $matches)) {
            $details[] = ucfirst($matches[0]);
        }

        // Check for combined shipping discount
        if (preg_match('/combin(?:ed|e)\s+(?:postage|shipping)/i', $content_lower)) {
            $details[] = 'Combined shipping available';
        }

        // Look for explicit shipping descriptions in specific HTML elements
        if (preg_match('/<(?:div|span|p)[^>]*class=["\'][^"\']*(?:ship|post|delivery)[^"\']*["\'][^>]*>(.*?)<\/(?:div|span|p)>/is', $html, $matches)) {
            $desc = strip_tags($matches[1]);
            $desc = trim($desc);
            if (!empty($desc) && strlen($desc) < 200) {
                $details[] = $desc;
            }
        }

        return !empty($details) ? implode(' | ', $details) : 'Shipping information not specified';
    }

    /**
     * Extract available shipping methods
     */
    private function extract_shipping_methods($content) {
        $methods = [];

        $shipping_types = [
            'standard' => 'Standard Shipping',
            'express' => 'Express Shipping',
            'priority' => 'Priority Shipping',
            'registered' => 'Registered Post',
            'courier' => 'Courier',
            'parcel post' => 'Parcel Post',
            'letter mail' => 'Letter Mail',
            'tracked' => 'Tracked Shipping',
        ];

        foreach ($shipping_types as $key => $label) {
            if (preg_match('/' . preg_quote($key, '/') . '/i', $content)) {
                $methods[] = $label;
            }
        }

        return array_unique($methods);
    }

    /**
     * Parse shipping from Discogs format
     */
    public function parse_discogs_shipping($shipping_element_html) {
        $info = $this->extract_shipping_info($shipping_element_html);

        // Discogs-specific parsing
        if (preg_match('/shipping from\s+([^<]+)/i', $shipping_element_html, $matches)) {
            $info['origin'] = trim(strip_tags($matches[1]));
        }

        return $info;
    }

    /**
     * Parse shipping from eBay format
     */
    public function parse_ebay_shipping($shipping_element_html) {
        $info = $this->extract_shipping_info($shipping_element_html);

        // eBay-specific parsing
        if (preg_match('/item location:\s*([^<]+)/i', $shipping_element_html, $matches)) {
            $info['origin'] = trim(strip_tags($matches[1]));
        }

        return $info;
    }

    /**
     * Calculate total shipping for an order
     */
    public function calculate_order_shipping($items) {
        $total_shipping = 0;
        $has_free_shipping = false;

        foreach ($items as $item) {
            $shipping_cost = floatval($item['shipping_cost'] ?? 0);

            if ($shipping_cost === 0) {
                $has_free_shipping = true;
            }

            $total_shipping += $shipping_cost * intval($item['quantity'] ?? 1);
        }

        // If any item has free shipping, could apply logic here
        // For now, just sum all shipping costs

        return round($total_shipping, 2);
    }

    /**
     * Format shipping info for display
     */
    public function format_shipping_display($shipping_info) {
        $parts = [];

        if (isset($shipping_info['cost']) && $shipping_info['cost'] > 0) {
            $parts[] = 'Cost: $' . number_format($shipping_info['cost'], 2);
        } elseif (isset($shipping_info['cost']) && $shipping_info['cost'] === 0) {
            $parts[] = 'FREE SHIPPING';
        }

        if (!empty($shipping_info['details'])) {
            $parts[] = $shipping_info['details'];
        }

        if (!empty($shipping_info['methods'])) {
            $parts[] = 'Methods: ' . implode(', ', $shipping_info['methods']);
        }

        if (!empty($shipping_info['origin'])) {
            $parts[] = 'From: ' . $shipping_info['origin'];
        }

        return implode(' | ', $parts);
    }
}
