<?php
// Import products view
if (!defined('ABSPATH')) exit;
?>

<div class="wrap">
    <h1>Import Products</h1>

    <div class="abmd-import-container">
        <div class="abmd-import-section">
            <h2>Import from URL</h2>
            <p>Enter a product URL or search results page URL from a supported site.</p>

            <form id="abmd-import-form">
                <table class="form-table">
                    <tr>
                        <th><label for="import_url">Product/Search URL:</label></th>
                        <td>
                            <input type="url" id="import_url" class="regular-text" placeholder="https://www.discogs.com/..." />
                            <p class="description">Supported sites: Discogs, eBay Australia</p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="button" id="test-scraper" class="button">Test Scraper</button>
                    <button type="submit" class="button button-primary">Import Products</button>
                </p>
            </form>

            <div id="import-results" style="display: none;">
                <h3>Import Results</h3>
                <div id="import-results-content"></div>
            </div>
        </div>

        <div class="abmd-import-section">
            <h2>Bulk Import from Configured Sites</h2>
            <p>Import products from all enabled source sites configured in Settings.</p>

            <button type="button" id="bulk-import" class="button button-primary">Run Bulk Import</button>

            <div id="bulk-import-progress" style="display: none;">
                <div class="abmd-progress-bar">
                    <div class="abmd-progress-fill"></div>
                </div>
                <p class="abmd-progress-text">Importing...</p>
            </div>
        </div>

        <div class="abmd-import-section">
            <h2>Supported Sites</h2>
            <ul>
                <li><strong>Discogs:</strong> Marketplace listings with Australian band merchandise</li>
                <li><strong>eBay Australia:</strong> Vinyl records and band merchandise</li>
                <li><strong>Generic Sites:</strong> Basic support for other sites (may require customization)</li>
            </ul>

            <p><strong>Tips:</strong></p>
            <ul>
                <li>Use search result pages to import multiple products at once</li>
                <li>Make sure the markup percentage is set correctly in Settings before importing</li>
                <li>Imported products will be published immediately to WooCommerce</li>
                <li>Images will be automatically downloaded and added to your media library</li>
            </ul>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Test scraper
    $('#test-scraper').on('click', function() {
        var url = $('#import_url').val();

        if (!url) {
            alert('Please enter a URL');
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true).text('Testing...');

        $.ajax({
            url: abmdAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'abmd_test_scraper',
                nonce: abmdAdmin.nonce,
                test_url: url
            },
            success: function(response) {
                if (response.success) {
                    $('#import-results-content').html('<pre>' + JSON.stringify(response.data, null, 2) + '</pre>');
                    $('#import-results').show();
                    alert('Scraper test successful! See results below.');
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function() {
                alert('Request failed');
            },
            complete: function() {
                btn.prop('disabled', false).text('Test Scraper');
            }
        });
    });

    // Import form
    $('#abmd-import-form').on('submit', function(e) {
        e.preventDefault();

        var url = $('#import_url').val();

        if (!url) {
            alert('Please enter a URL');
            return;
        }

        if (!confirm('Import products from this URL?')) {
            return;
        }

        $('#import-results-content').html('<p>Importing...</p>');
        $('#import-results').show();

        $.ajax({
            url: abmdAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'abmd_import_product',
                nonce: abmdAdmin.nonce,
                product_url: url
            },
            success: function(response) {
                if (response.success) {
                    $('#import-results-content').html(
                        '<div class="notice notice-success"><p>' + response.data.message + '</p></div>'
                    );
                } else {
                    $('#import-results-content').html(
                        '<div class="notice notice-error"><p>' + response.data.message + '</p></div>'
                    );
                }
            },
            error: function() {
                $('#import-results-content').html(
                    '<div class="notice notice-error"><p>Request failed</p></div>'
                );
            }
        });
    });

    // Bulk import
    $('#bulk-import').on('click', function() {
        if (!confirm('Start bulk import from all configured sites?')) {
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true);
        $('#bulk-import-progress').show();

        // This would trigger a longer-running process
        // For now, just show a message
        alert('Bulk import has been queued. This may take several minutes. Check the Source Products page to see progress.');

        setTimeout(function() {
            btn.prop('disabled', false);
            $('#bulk-import-progress').hide();
        }, 2000);
    });
});
</script>
