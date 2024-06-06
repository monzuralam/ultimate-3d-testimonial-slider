<?php

namespace UTS;

// don't call the file directly
if (!defined('ABSPATH')) {
    wp_die(__('You can\'t access this page', 'uts'));
}

/**
 * Cpt
 */
class Cpt {
    /**
     * The single instance of the class.
     *
     * @var Cpt
     * @since 1.0.1
     */
    private static $instance = null;

    public function __construct() {
        add_action('init', array($this, 'setup_post_type'));
    }

    /**
     * Register Post Type
     *
     * @return void
     */
    public function setup_post_type() {

        /**
         * Post Type: Ultimate Testimonial Slider
         */
        $labels = [
            "name" => __("Ultimate 3D Testimonial Sliders", "uts"),
            "singular_name" => __("Ultimate 3D Testimonial Slider", "uts"),
            "menu_name" => __("Ultimate 3D Testimonial Slider", "uts"),
            "all_items" => __("All Items", "uts"),
            "featured_image" => __("Client Featured Image", "uts"),
            "set_featured_image" => __("Set Client Featured Image", "uts"),
            "remove_featured_image" => __("Remove Client Featured Image", "uts"),
            "use_featured_image" => __("Use Client Featured Image", "uts"),
        ];

        $args = [
            "label" => __("Ultimate 3D Testimonial Sliders", "uts"),
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
            "rewrite" => ["slug" => "uts", "with_front" => true],
            "query_var" => true,
            "menu_icon" => "dashicons-slides",
            "supports" => ["title", "editor", "thumbnail"],
            "show_in_graphql" => false,
        ];

        register_post_type("uts", $args);

        /**
         * Post Type: Shortcode Builder.
         */

        $labels = [
            "name" => esc_html__("Shortcode Builder", "uts"),
            "singular_name" => esc_html__("Shortcode Builder", "uts"),
            "menu_name" => esc_html__("Shortcode Builder", "uts"),
            "all_items" => esc_html__("Shortcode Builder", "uts"),
            "add_new" => esc_html__("Add Shortcode", "uts"),
            "add_new_item" => esc_html__("Add Shortcode", "uts"),
            "edit_item" => esc_html__("Edit Shortcode", "uts"),
            "new_item" => esc_html__("New Shortcode", "uts"),
        ];

        $args = [
            "label" => esc_html__("Shortcode Builder", "uts"),
            "labels" => $labels,
            "description" => "",
            "public" => true,
            "publicly_queryable" => true,
            "show_ui" => true,
            "show_in_rest" => true,
            "rest_base" => "",
            "rest_controller_class" => "WP_REST_UTS_Shortcode_Builder_Controller",
            "rest_namespace" => "wp/v2",
            "has_archive" => false,
            "show_in_menu" => "edit.php?post_type=uts",
            "show_in_nav_menus" => true,
            "delete_with_user" => false,
            "exclude_from_search" => false,
            "capability_type" => "post",
            "map_meta_cap" => true,
            "hierarchical" => false,
            "can_export" => false,
            "rewrite" => ["slug" => "shortcode-builder", "with_front" => true],
            "query_var" => true,
            "supports" => ["title"],
            "show_in_graphql" => false,
        ];

        register_post_type("shortcode-builder", $args);
    }

    /**
     * Active
     *
     * @return void
     */
    public static function active() {
        self::$instance->setup_post_type();
        flush_rewrite_rules();
    }

    /**
     * Instance
     */
    public static function instance() {
        if (null == self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

Cpt::instance();
