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
        add_filter('manage_shortcode-builder_posts_columns', array($this, 'add_columns'), 100);
        add_action('manage_shortcode-builder_posts_custom_column', array($this, 'column_content'), 10, 2);
    }

    /**
     * Add Column
     *
     * @param array $columns
     * @return void
     */
    public function add_columns($columns) {
        unset( $columns['date'] );
        $columns['shortcode']   = __('Shortcode', 'uts');

        return $columns;
    }

    /**
     * Column Content
     *
     * @param array $column
     * @param integer $post_id
     * @return void
     */
    public function column_content($column, $post_id) {
        switch ($column) {
            case 'shortcode':
                ob_start();
?>
                <input type="text" value="<?php echo esc_attr('[uts id="' . $post_id . '"]') ?>">
                <button type="button" class="button uts-copy-shortcode" title="<?php echo esc_attr__('Copy', 'uts'); ?>">
                    <span class="dashicons dashicons-admin-page"></span>
                </button>
<?php
                echo ob_get_clean();
                break;
        }
    }

    /**
     * Instance
     *
     * @return void
     */
    public static function instance() {
        if (null == self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

Hooks::instance();
