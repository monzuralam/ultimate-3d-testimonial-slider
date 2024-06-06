<?php

/**
 * Plugin Name:       Ultimate 3D Testimonial Slider
 * Plugin URI:        https://wordpress.org/plugins/ultimate-3d-testimonial-slider
 * Description:       Easily create responsive 3D carousel slider for testimonial and insert into any page or page via shortcode.
 * Version:           1.0.1
 * Author:            Monzur Alam
 * Author URI:        https://profiles.wordpress.org/monzuralam
 * Text Domain:       uts
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/monzuralam/ultimate-3d-testimonial-slider
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    wp_die(__('You can\'t access this page', 'uts'));
}

/**
 * define constants
 */
define('UTS_VERSION', '1.0.1');
define('UTS_FILE', __FILE__);
define('UTS_PATH', dirname(UTS_FILE));
define('UTS_URL', plugins_url('', UTS_FILE));
define('UTS_ASSETS', UTS_URL . '/assets');
define('UTS_INCLUDES', UTS_PATH . '/includes');

/**
 * Activate the plugin.
 */
function uts_active() {
    // Trigger our function that registers the cutsom post type plugin.
    UTS\Cpt::active();
}
register_activation_hook(__FILE__, 'uts_active');

/**
 * Deactivation hook.
 */
function uts_deactivate() {
    // Unregister the post type, so the rules are no longer in memory.
    unregister_post_type('uts');
    // Clear the permalinks to remove our post type's rules from the database.
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'uts_deactivate');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 *
 * @since 1.0.0
 */
add_action('plugins_loaded', function () {
    include_once UTS_INCLUDES . '/Main.php';
});
