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
 * Custom Meta box
 */
require_once('includes/metabox.php');