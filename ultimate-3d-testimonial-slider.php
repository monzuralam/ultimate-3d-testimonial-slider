<?php

/**
 * Plugin Name:       Ultimate 3D Testimonial Slider
 * Plugin URI:        https://wordpress.org/plugins/ultimate-3d-testimonial-slider
 * Description:       Easily create responsive 3D carousel slider for testimonial and insert into any page or page via shortcode.
 * Version:           1.0.0
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
define( 'UTS_VERSION', '1.0.0' );
define( 'UTS_FILE', __FILE__ );
define( 'UTS_PATH', dirname( UTS_FILE ) );
define( 'UTS_URL', plugins_url( '', UTS_FILE ) );
define( 'UTS_ASSETS', UTS_URL . '/assets' );
define( 'UTS_INCLUDES', UTS_PATH . '/includes' );

/**
 * Enqueue CSS & JS
 */
if (!function_exists('uts_assets')) {
    function uts_assets(){
        wp_enqueue_style('uts', UTS_ASSETS . '/css/uts.css', array(), '1.0.0', 'all');
        wp_enqueue_script('jquery');
        wp_enqueue_script('modernizr', UTS_ASSETS . '/js/modernizr.min.js', array('jquery'), '1.0.0', false);
        wp_enqueue_script('gallery', UTS_ASSETS . '/js/jquery.gallery.js', array('jquery'), '1.0.0', true);
        wp_enqueue_script('uts', UTS_ASSETS . '/js/uts.js', array('jquery','gallery'), '1.0.0', true);
    }
    add_action('wp_enqueue_scripts', 'uts_assets');
}

/**
 * Activate the plugin.
 */
function uts_active() { 
    // Trigger our function that registers the cutsom post type plugin.
    uts_setup_post_type(); 
    // Clear the permalinks after the post type has been registered.
    flush_rewrite_rules(); 
}
register_activation_hook( __FILE__, 'uts_active' );

/**
 * Deactivation hook.
 */
function uts_deactivate() {
    // Unregister the post type, so the rules are no longer in memory.
    unregister_post_type( 'uts' );
    // Clear the permalinks to remove our post type's rules from the database.
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'uts_deactivate' );

/**
 * Cutsom Post Type
 */
require_once UTS_INCLUDES . '/cpt.php';

/**
 * Cutsom Meta box
 */
require_once UTS_INCLUDES . '/metabox.php';

/**
 * Shortcode
 */
require_once UTS_INCLUDES . '/shortcode.php';

/**
 * Menu
 */
require_once UTS_INCLUDES . '/menu.php';