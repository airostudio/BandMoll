<?php
use GuzzleHttp\Client;
use Sunra\PhpSimple\HtmlDomParser;

/**
 * Web scraper for second-hand record sites
 */
class ABMD_Scraper {

    private $client;
    private $logger;

    public function __construct() {
        $this->client = new Client([
            'timeout'  => 30,
            'verify' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);
    }

    /**
     * Scrape products from a site configuration
     */
    public function scrape_site($site_config) {
        $site_name = $site_config['name'] ?? 'Unknown';
        $base_url = $site_config['url'] ?? '';
        $search_query = $site_config['search_query'] ?? '';

        // Determine site type and use appropriate scraper
        if (strpos($base_url, 'discogs.com') !== false) {
            return $this->scrape_discogs($base_url, $search_query);
        } elseif (strpos($base_url, 'ebay.com.au') !== false) {
            return $this->scrape_ebay_au($base_url, $search_query);
        } else {
            // Generic scraper for unknown sites
            return $this->scrape_generic($base_url, $search_query);
        }
    }

    /**
     * Scrape single product from URL
     */
    public function scrape_single_product($url) {
        try {
            $response = $this->client->request('GET', $url);
            $html = (string) $response->getBody();
            $dom = HtmlDomParser::str_get_html($html);

            if (!$dom) {
                throw new Exception('Failed to parse HTML');
            }

            // Determine site type
            if (strpos($url, 'discogs.com') !== false) {
                return $this->parse_discogs_product($dom, $url);
            } elseif (strpos($url, 'ebay.com.au') !== false) {
                return $this->parse_ebay_product($dom, $url);
            } else {
                return $this->parse_generic_product($dom, $url);
            }

        } catch (Exception $e) {
            error_log('ABMD Scraper Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Scrape Discogs listings
     */
    private function scrape_discogs($base_url, $search_query) {
        $products = [];

        // Discogs API would be better, but for scraping:
        $search_url = $base_url . '/sell/list?q=' . urlencode($search_query) . '&format=Vinyl&ships_from=Australia';

        try {
            $response = $this->client->request('GET', $search_url);
            $html = (string) $response->getBody();
            $dom = HtmlDomParser::str_get_html($html);

            if (!$dom) {
                return $products;
            }

            // Parse Discogs marketplace listings
            foreach ($dom->find('.shortcut_navigable') as $item) {
                $product = $this->parse_discogs_listing($item, $base_url);
                if ($product) {
                    $products[] = $product;
                }
            }

        } catch (Exception $e) {
            error_log('Discogs scrape error: ' . $e->getMessage());
        }

        return $products;
    }

    /**
     * Parse Discogs listing item
     */
    private function parse_discogs_listing($item, $base_url) {
        try {
            $title_elem = $item->find('.item_description a', 0);
            $price_elem = $item->find('.price', 0);
            $condition_elem = $item->find('.item_condition', 0);
            $seller_elem = $item->find('.seller_info a', 0);

            if (!$title_elem || !$price_elem) {
                return null;
            }

            $title = trim($title_elem->plaintext);
            $product_url = $base_url . $title_elem->href;
            $price_text = trim($price_elem->plaintext);

            // Extract price (remove currency symbols)
            preg_match('/[\d,]+\.?\d*/', $price_text, $price_matches);
            $price = isset($price_matches[0]) ? floatval(str_replace(',', '', $price_matches[0])) : 0;

            $shipping_parser = new ABMD_Shipping_Parser();
            $shipping_info = $shipping_parser->extract_shipping_info($item->innertext);

            return [
                'source_site' => 'Discogs',
                'source_url' => $product_url,
                'title' => $title,
                'price' => $price,
                'condition' => $condition_elem ? trim($condition_elem->plaintext) : 'Used',
                'shipping_cost' => $shipping_info['cost'] ?? 0,
                'shipping_info' => $shipping_info['details'] ?? '',
                'artist' => $this->extract_artist_from_title($title),
            ];

        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Parse Discogs product page
     */
    private function parse_discogs_product($dom, $url) {
        $data = [
            'source_site' => 'Discogs',
            'source_url' => $url,
        ];

        // Extract title
        $title_elem = $dom->find('h1.hide_mobile', 0);
        $data['title'] = $title_elem ? trim($title_elem->plaintext) : 'Unknown Title';

        // Extract price
        $price_elem = $dom->find('.price', 0);
        if ($price_elem) {
            preg_match('/[\d,]+\.?\d*/', $price_elem->plaintext, $matches);
            $data['price'] = isset($matches[0]) ? floatval(str_replace(',', '', $matches[0])) : 0;
        } else {
            $data['price'] = 0;
        }

        // Extract images
        $image_elem = $dom->find('.thumbnail_center img', 0);
        $data['image_urls'] = $image_elem ? [$image_elem->src] : [];

        // Extract description
        $desc_elem = $dom->find('.item_description_full', 0);
        $data['description'] = $desc_elem ? trim($desc_elem->plaintext) : '';

        // Extract shipping
        $shipping_parser = new ABMD_Shipping_Parser();
        $shipping_info = $shipping_parser->extract_shipping_info($dom->innertext);
        $data['shipping_cost'] = $shipping_info['cost'] ?? 0;
        $data['shipping_info'] = $shipping_info['details'] ?? '';

        $data['artist'] = $this->extract_artist_from_title($data['title']);
        $data['condition'] = 'Used';

        return $data;
    }

    /**
     * Scrape eBay Australia
     */
    private function scrape_ebay_au($base_url, $search_query) {
        $products = [];
        $search_url = 'https://www.ebay.com.au/sch/i.html?_nkw=' . urlencode($search_query . ' vinyl australian band');

        try {
            $response = $this->client->request('GET', $search_url);
            $html = (string) $response->getBody();
            $dom = HtmlDomParser::str_get_html($html);

            if (!$dom) {
                return $products;
            }

            foreach ($dom->find('.s-item') as $item) {
                $product = $this->parse_ebay_listing($item);
                if ($product) {
                    $products[] = $product;
                }
            }

        } catch (Exception $e) {
            error_log('eBay scrape error: ' . $e->getMessage());
        }

        return $products;
    }

    /**
     * Parse eBay listing
     */
    private function parse_ebay_listing($item) {
        try {
            $title_elem = $item->find('.s-item__title', 0);
            $price_elem = $item->find('.s-item__price', 0);
            $link_elem = $item->find('.s-item__link', 0);
            $image_elem = $item->find('.s-item__image-wrapper img', 0);

            if (!$title_elem || !$price_elem || !$link_elem) {
                return null;
            }

            $price_text = trim($price_elem->plaintext);
            preg_match('/[\d,]+\.?\d*/', $price_text, $price_matches);
            $price = isset($price_matches[0]) ? floatval(str_replace(',', '', $price_matches[0])) : 0;

            $shipping_parser = new ABMD_Shipping_Parser();
            $shipping_info = $shipping_parser->extract_shipping_info($item->innertext);

            return [
                'source_site' => 'eBay Australia',
                'source_url' => $link_elem->href,
                'title' => trim($title_elem->plaintext),
                'price' => $price,
                'image_urls' => $image_elem ? [$image_elem->src] : [],
                'shipping_cost' => $shipping_info['cost'] ?? 0,
                'shipping_info' => $shipping_info['details'] ?? '',
                'artist' => $this->extract_artist_from_title(trim($title_elem->plaintext)),
                'condition' => 'Used',
            ];

        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Parse eBay product page
     */
    private function parse_ebay_product($dom, $url) {
        // Similar to Discogs but for eBay structure
        return [
            'source_site' => 'eBay Australia',
            'source_url' => $url,
            'title' => 'eBay Product',
            'price' => 0,
        ];
    }

    /**
     * Generic scraper for unknown sites
     */
    private function scrape_generic($url, $search_query = '') {
        // Basic generic scraper - would need customization per site
        return [];
    }

    /**
     * Parse generic product
     */
    private function parse_generic_product($dom, $url) {
        // Attempt to find common product elements
        $data = [
            'source_site' => parse_url($url, PHP_URL_HOST),
            'source_url' => $url,
            'title' => 'Generic Product',
            'price' => 0,
        ];

        return $data;
    }

    /**
     * Extract artist name from title
     */
    private function extract_artist_from_title($title) {
        // Simple extraction - takes first part before dash or parenthesis
        $parts = preg_split('/[-\(]/', $title);
        return isset($parts[0]) ? trim($parts[0]) : '';
    }

    /**
     * Test URL scraping
     */
    public function test_url($url) {
        try {
            $product = $this->scrape_single_product($url);
            return [
                'success' => true,
                'product' => $product,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Scrape URL for multiple products
     */
    public function scrape_url($url) {
        $site_config = [
            'url' => $url,
            'search_query' => '',
        ];
        return $this->scrape_site($site_config);
    }
}
