<?php

namespace UTS;

// don't call the file directly
if (!defined('ABSPATH')) {
    wp_die(__('You can\'t access this page', 'uts'));
}

/**
 * Enqueue
 */
class Enqueue {
    /**
     * The single instance of the class.
     *
     * @var Enqueue
     * @since 1.0.1
     */
    private static $instance = null;

    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'admin_assets'));
    }

    /**
     * Frontend Assets
     * @since 1.0.1
     * @author  Monzur Alam
     */
    public function frontend_assets() {
        wp_register_style('uts', UTS_ASSETS . '/css/uts.css', array(), UTS_VERSION, 'all');

        wp_enqueue_script('jquery');
        wp_register_script('modernizr', UTS_ASSETS . '/js/modernizr.min.js', array('jquery'), UTS_VERSION, false);
        wp_register_script('gallery', UTS_ASSETS . '/js/jquery.gallery.js', array('jquery'), UTS_VERSION, true);
        wp_register_script('uts', UTS_ASSETS . '/js/uts.js', array('jquery', 'gallery'), UTS_VERSION, true);
    }

    /**
     * Admin Assets
     * @since 1.0.0
     * @author  Monzur Alam
     */
    public function admin_assets($hook) {
        wp_enqueue_style('uts-admin', UTS_ASSETS . '/css/uts-admin.css', array(), UTS_VERSION, 'all');
        wp_enqueue_script('uts-admin', UTS_ASSETS . '/js/uts-admin.js', array('jquery'), UTS_VERSION, true);
    }

    /**
     * Instance
     *
     * @return void
     */
    public static function instance(){
        if( null == self::$instance ){
            self::$instance = new self();
        }

        return self::$instance;
    }
}

Enqueue::instance();
