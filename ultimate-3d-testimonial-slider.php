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

define('UTS_PATH', plugin_dir_path(__FILE__));
define('UTS_URL', plugin_dir_url(__FILE__));

/**
 * Enqueue CSS & JS
 */
if (!function_exists('uts_assets')) {
    function uts_assets(){
        wp_enqueue_style('uts', UTS_URL . 'assets/css/uts.css', array(), '1.0.0', 'all');
        wp_enqueue_script('jquery');
        wp_enqueue_script('modernizr', UTS_URL . 'assets/js/modernizr.min.js', array('jquery'), '1.0.0', false);
        wp_enqueue_script('gallery', UTS_URL . 'assets/js/jquery.gallery.js', array('jquery'), '1.0.0', true);
        wp_enqueue_script('uts', UTS_URL . 'assets/js/uts.js', array('jquery','gallery'), '1.0.0', true);
    }
    add_action('wp_enqueue_scripts', 'uts_assets');
}

/**
 * Register Post Type
 */
if( !function_exists('uts_setup_post_type')){
    function uts_setup_post_type() {
        $labels = [
            "name" => __( "Ultimate 3D Testimonial Sliders", "uts" ),
            "singular_name" => __( "Ultimate 3D Testimonial Slider", "uts" ),
            "menu_name" => __( "Ultimate 3D Testimonial Slider", "uts" ),
            "all_items" => __( "All Items", "uts" ),
            "featured_image" => __( "Client Featured Image", "uts" ),
            "set_featured_image" => __( "Set Client Featured Image", "uts" ),
            "remove_featured_image" => __( "Remove Client Featured Image", "uts" ),
            "use_featured_image" => __( "Use Client Featured Image", "uts" ),
        ];
    
        $args = [
            "label" => __( "Ultimate 3D Testimonial Sliders", "uts" ),
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
            "rewrite" => [ "slug" => "uts", "with_front" => true ],
            "query_var" => true,
            "menu_icon" => "dashicons-slides",
            "supports" => [ "title", "editor", "thumbnail" ],
            "show_in_graphql" => false,
        ];
    
        register_post_type( "uts", $args );
    }
    add_action( 'init', 'uts_setup_post_type' );
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
 * Cutsom Meta box
 */
require_once('includes/metabox.php');

/**
 * Shortcode
 */
require_once('includes/shortcode.php');