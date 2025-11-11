/**
 * Admin JavaScript for Aussie Band Merch Dropship
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // Initialize tooltips if available
        if ($.fn.tooltip) {
            $('[data-tooltip]').tooltip();
        }

        // Confirm before enabling auto-ordering
        $('#auto_order_enabled').on('change', function() {
            if ($(this).is(':checked')) {
                if (!confirm('WARNING: Automated ordering will place real orders on source sites. Are you sure you want to enable this?')) {
                    $(this).prop('checked', false);
                }
            }
        });

        // Import progress tracking
        var importInProgress = false;

        // Handle import form submission
        $('#abmd-import-form').on('submit', function(e) {
            if (importInProgress) {
                e.preventDefault();
                alert('Import already in progress');
                return false;
            }
        });

        // Refresh orders page periodically if there are pending orders
        if ($('.abmd-status-pending').length > 0) {
            // Refresh every 60 seconds
            setTimeout(function() {
                location.reload();
            }, 60000);
        }

        // Auto-save settings notification
        $('form').on('submit', function() {
            $(this).find('button[type="submit"]').prop('disabled', true).text('Saving...');
        });

        // Validate markup percentage
        $('#markup_percentage').on('change', function() {
            var value = parseFloat($(this).val());
            if (value < 0) {
                alert('Markup percentage cannot be negative');
                $(this).val(0);
            } else if (value > 1000) {
                alert('Markup percentage seems unusually high. Please double-check.');
            }
        });

        // Test API credentials (placeholder)
        $('.test-api-credentials').on('click', function() {
            var site = $(this).data('site');
            alert('API test for ' + site + ' - This feature is coming soon');
        });

        // Highlight rows on hover
        $('.wp-list-table tbody tr').hover(
            function() {
                $(this).css('background-color', '#f0f0f1');
            },
            function() {
                $(this).css('background-color', '');
            }
        );

    });

})(jQuery);
