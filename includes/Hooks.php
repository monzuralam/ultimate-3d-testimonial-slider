<?php

namespace UTS;

// don't call the file directly
if (!defined('ABSPATH')) {
    wp_die(__('You can\'t access this page', 'uts'));
}

/**
 * Hooks
 */
class Hooks {
    /**
     * The single instance of the class.
     *
     * @var Hooks
     * @since 1.0.1
     */
    private static $instance = null;

    /**
     * Constructor
     */
    public function __construct() {

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

Hooks::instance();
