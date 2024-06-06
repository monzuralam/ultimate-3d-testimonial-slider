<?php

// don't call the file directly
if (!defined('ABSPATH')) {
    wp_die(__('You can\'t access this page', 'uts'));
}

/**
 * Main
 * @since 1.0.1
 */
final class UTS {
    /**
     * The single instance of the class.
     *
     * @var Main
     * @since 1.0.1
     */
    protected static $instance = null;

    public function __construct(){
        $this->includes();
    }

    /**
     * Include required core files for plugin.
     *
     * @return void
     */
    public function includes(){
        include_once UTS_INCLUDES . '/Enqueue.php';
        include_once UTS_INCLUDES . '/Hooks.php';
        include_once UTS_INCLUDES . '/Cpt.php';
        include_once UTS_INCLUDES . '/Metabox.php';
        include_once UTS_INCLUDES . '/Menu.php';
        include_once UTS_INCLUDES . '/Shortcode.php';
        include_once UTS_INCLUDES . '/functions.php';
    }

    /**
     * Main Instance
     *
     * @static
     */
    public static function instance(){
        if( null == self::$instance){
            self::$instance = new self();
        }

        return self::$instance;
    }
}

// Kickoff uts
if( ! function_exists('uts') ){
    function uts(){
        return UTS::instance();
    }
}

uts();
