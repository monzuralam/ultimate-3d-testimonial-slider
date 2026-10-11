<?php
/**
 * Remove plugin data on uninstall: options and the saved shortcodes table.
 *
 * Testimonial posts are kept so content isn't lost. Runs without the plugin
 * loaded, so it doesn't use the plugin's classes. On multisite every site
 * has its own table and options.
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit; // Exit if accessed directly.
}

/**
 * Remove the plugin's data from the current site.
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
function uts_uninstall_site() {
    global $wpdb;

    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}uts_shortcodes"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

    delete_option('uts_settings');
    delete_option('uts_activation_redirect');
    delete_option('uts_db_version');
}

if (is_multisite()) {
    foreach (get_sites(array('fields' => 'ids', 'number' => 0)) as $uts_site_id) {
        switch_to_blog($uts_site_id);
        uts_uninstall_site();
        restore_current_blog();
    }
} else {
    uts_uninstall_site();
}
