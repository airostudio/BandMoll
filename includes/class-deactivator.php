<?php
/**
 * Fired during plugin deactivation
 */
class ABMD_Deactivator {

    /**
     * Deactivation logic
     */
    public static function deactivate() {
        // Clear scheduled cron jobs
        $timestamp = wp_next_scheduled('abmd_auto_sync_products');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'abmd_auto_sync_products');
        }

        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
