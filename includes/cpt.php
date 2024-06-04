<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
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