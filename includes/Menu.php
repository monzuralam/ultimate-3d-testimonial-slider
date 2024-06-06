<?php

namespace UTS;

if (!defined('ABSPATH')) {
    wp_die(__('You can\'t access this page', 'uts'));
}

/**
 * Menu
 */
class Menu {
    /**
     * The single instance of the class.
     *
     * @var Enqueue
     * @since 1.0.1
     */
    protected static $instance = null;

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'register_menu'));
    }

    /**
     * Register Menu
     */
    public function register_menu() {
        add_submenu_page(
            'edit.php?post_type=uts',
            __('Getting Started'),
            __('Getting Started'),
            'manage_options',
            'getting-started',
            array($this, 'getting_started')
        );
    }

    public function getting_started() {
?>
        <div class="wrap"></div>
<?php
    }

    /**
     * Instance
     */
    public static function instance(){
        if( null == self::$instance ){
            self::$instance = new self();
        }

        return self::$instance;
    }
}

Menu::instance();
