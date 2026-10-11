<?php

/**
 * Plugin Name:       Ultimate 3D Testimonial Slider
 * Plugin URI:        https://wordpress.org/plugins/ultimate-3d-testimonial-slider
 * Description:       Easily create responsive 3D carousel slider for testimonial and insert into any page or page via shortcode.
 * Version:           1.1.0
 * Requires at least: 5.8
 * Requires PHP:      5.6
 * Author:            Monzur Alam
 * Author URI:        https://profiles.wordpress.org/monzuralam
 * Text Domain:       uts
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/monzuralam/ultimate-3d-testimonial-slider
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * define constants
 */
define( 'UTS_VERSION', '1.1.0' );
define( 'UTS_FILE', __FILE__ );
define( 'UTS_PATH', dirname( UTS_FILE ) );
define( 'UTS_URL', plugins_url( '', UTS_FILE ) );
define( 'UTS_ASSETS', UTS_URL . '/assets' );
define( 'UTS_INCLUDES', UTS_PATH . '/includes' );
define( 'UTS_BUILD', UTS_PATH . '/build' );
define( 'UTS_BUILD_URL', UTS_URL . '/build' );

/**
 * Shared helpers (loaded first)
 */
require_once UTS_INCLUDES . '/functions.php';

/**
 * Plugin bootstrap
 */
require_once UTS_INCLUDES . '/class-plugin.php';

/**
 * Assets
 */
require_once UTS_INCLUDES . '/class-assets.php';

/**
 * Settings
 */
require_once UTS_INCLUDES . '/class-settings.php';

/**
 * Cutsom Post Type
 */
require_once UTS_INCLUDES . '/class-cpt.php';

/**
 * Cutsom Meta box
 */
require_once UTS_INCLUDES . '/class-metabox.php';

/**
 * Shortcode
 */
require_once UTS_INCLUDES . '/class-shortcode.php';

/**
 * Menu
 */
require_once UTS_INCLUDES . '/class-menu.php';

/**
 * Testimonials list columns and filters
 */
require_once UTS_INCLUDES . '/class-admin-columns.php';

/**
 * Getting Started page
 */
require_once UTS_INCLUDES . '/class-getting-started.php';

/**
 * Activation and deactivation
 */
register_activation_hook( __FILE__, array( 'UTS\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'UTS\Plugin', 'deactivate' ) );

/**
 * Start the plugin
 */
UTS\Plugin::instance()->boot();
