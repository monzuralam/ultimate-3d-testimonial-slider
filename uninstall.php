<?php
/**
 * Uninstall: keep or remove the plugin data, as chosen on the Settings page
 * (Advanced → "Keep data when the plugin is deleted", option `uts_keep_data`).
 *
 * - Keep (default): the settings, saved sliders table and schema version stay,
 *   so reinstalling the plugin brings everything back.
 * - Remove: the saved sliders table and every plugin option are deleted.
 *
 * Testimonial posts are kept either way, so content isn't lost. Runs without
 * the plugin loaded, so it doesn't use the plugin's classes. On multisite
 * each site has its own data and its own choice.
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit; // Exit if accessed directly.
}

/**
 * Keep or remove the plugin's data on the current site.
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
function uts_uninstall_site() {
    global $wpdb;

    // One-time flag, never worth keeping.
    delete_option('uts_activation_redirect');

    // Missing option = never changed = keep (the default).
    if (rest_sanitize_boolean(get_option('uts_keep_data', true))) {
        return;
    }

    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}uts_shortcodes"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

    delete_option('uts_settings');
    delete_option('uts_db_version');
    delete_option('uts_keep_data');
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
