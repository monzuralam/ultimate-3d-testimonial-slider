<?php

/**
 * Plugin Name:       Ultimate 3D Slider Testimonial
 * Plugin URI:        https://profiles.wordpress.org/monzuralam
 * Description:       Easily create responsive 3D carousel slider for testimonial and insert into any page or page via shortcode.
 * Version:           1.0.0
 * Author:            Monzur Alam
 * Author URI:        https://profiles.wordpress.org/monzuralam
 * Text Domain:       ust
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/monzuralam/ultimate-3d-slider-testimonial
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('UST_PATH', plugin_dir_path(__FILE__));
define('UST_URL', plugin_dir_url(__FILE__));

/**
 * Enqueue CSS & JS
 */
if (!function_exists('ust_assets')) {
    function ust_assets(){
        wp_enqueue_style('ust', UST_URL . 'assets/css/ust.css', array(), '1.0.0', 'all');
        wp_enqueue_script('jquery');
        wp_enqueue_script('modernizr', UST_URL . 'assets/js/modernizr.min.js', array('jquery'), '1.0.0', false);
        wp_enqueue_script('gallery', UST_URL . 'assets/js/jquery.gallery.js', array('jquery'), '1.0.0', true);
        wp_enqueue_script('ust', UST_URL . 'assets/js/ust.js', array('jquery','gallery'), '1.0.0', true);
    }
    add_action('wp_enqueue_scripts', 'ust_assets');
}

/**
 * Register Post Type
 */
if( !function_exists('ust_setup_post_type')){
    function ust_setup_post_type() {
        $labels = [
            "name" => __( "Ultimate 3D Slider Testimonials", "ust" ),
            "singular_name" => __( "Ultimate 3D Slider Testimonial", "ust" ),
            "menu_name" => __( "Ultimate 3D Slider Testimonial", "ust" ),
            "all_items" => __( "All Items", "ust" ),
            "featured_image" => __( "Client Featured Image", "ust" ),
            "set_featured_image" => __( "Set Client Featured Image", "ust" ),
            "remove_featured_image" => __( "Remove Client Featured Image", "ust" ),
            "use_featured_image" => __( "Use Client Featured Image", "ust" ),
        ];
    
        $args = [
            "label" => __( "Ultimate 3D Slider Testimonials", "ust" ),
            "labels" => $labels,
            "description" => "",
            "public" => true,
            "publicly_queryable" => true,
            "show_ui" => true,
            "show_in_rest" => false,
            "rest_base" => "",
            "rest_controller_class" => "WP_REST_Posts_Controller",
            "has_archive" => false,
            "show_in_menu" => true,
            "show_in_nav_menus" => true,
            "delete_with_user" => false,
            "exclude_from_search" => false,
            "capability_type" => "post",
            "map_meta_cap" => true,
            "hierarchical" => false,
            "can_export" => false,
            "rewrite" => [ "slug" => "ust", "with_front" => true ],
            "query_var" => true,
            "menu_icon" => "dashicons-slides",
            "supports" => [ "title", "editor", "thumbnail" ],
            "show_in_graphql" => false,
        ];
    
        register_post_type( "ust", $args );
    }
    add_action( 'init', 'ust_setup_post_type' );
}

/**
 * Activate the plugin.
 */
function ust_active() { 
    // Trigger our function that registers the custom post type plugin.
    ust_setup_post_type(); 
    // Clear the permalinks after the post type has been registered.
    flush_rewrite_rules(); 
}
register_activation_hook( __FILE__, 'ust_active' );

/**
 * Deactivation hook.
 */
function ust_deactivate() {
    // Unregister the post type, so the rules are no longer in memory.
    unregister_post_type( 'ust' );
    // Clear the permalinks to remove our post type's rules from the database.
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'ust_deactivate' );

/**
 * Custom Meta box
 */
require_once('includes/metabox.php');

/**
 * Shortcode
 */
require_once('includes/shortcode.php');