<?php

namespace UTS;

// don't call the file directly
if (!defined('ABSPATH')) {
    wp_die(__('You can\'t access this page', 'uts'));
}

/**
 * Metabox
 */
class Metabox {
    /**
     * The single instance of the class.
     *
     * @var Metabox
     * @since 1.0.1
     */
    private static $instance = null;

    /**
     * Constructor
     */
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'register_metabox'));
        add_action('save_post', array($this, 'save_metabox'));
    }

    public function register_metabox() {
        // Define the screens (post types) and corresponding callbacks
        $meta_boxes = [
            'uts' => [
                'id'        => 'uts_custom_metabox',
                'title'     => __('Client Additional', 'ust'),
                'callback'  => array($this, 'custom_metabox'),
                'context'   =>  'normal',
            ],
            'shortcode-builder' => [
                'id'        => 'uts_shortcode_metabox',
                'title'     => __('Shortcode', 'ust'),
                'callback'  => array($this, 'shortcode_metabox'),
                'context'   =>  'side',
            ]
        ];

        // Register the meta boxes for each screen
        foreach ($meta_boxes as $screen => $meta_box) {
            add_meta_box(
                $meta_box['id'],          // Unique ID
                $meta_box['title'],       // Box title
                $meta_box['callback'],    // Content callback, must be of type callable
                $screen,                  // Post type
                $meta_box['context']      // Context: 'normal', 'advanced', or 'side'
            );
        }
    }

    /**
     * Custom Metabox Callback
     *
     * @param [type] $post
     * @return void
     */
    public function custom_metabox($post) {
        wp_nonce_field('uts_metabox_nonce', 'uts_metabox_nonce_field');

        $designation = get_post_meta($post->ID, 'uts_designation', true);
    ?>
        <label for="uts_designation"><?php _e('Position & Company Name', 'ust'); ?></label>
        <input type="text" name="uts_designation" id="uts_designation" class="regular-text" value="<?php echo esc_attr($designation); ?>" placeholder="<?php _e('CEO, Company Name') ?>">
    <?php
    }

    public function shortcode_metabox($post) {
        // Display the shortcode with the post ID
        $shortcode = sprintf('[uts id="%d"]', $post->ID);
    ?>
        <div class="uts-shortcode-wrap">
            <input type="text" value="<?php echo esc_attr($shortcode) ?>" readonly />
            <button type="button" class="button uts-copy-shortcode" title="<?php echo esc_attr__('Copy', 'uts'); ?>">
                <span class="dashicons dashicons-admin-page"></span>
            </button>
        </div>
    <?php
    }

    /**
     * Save Metabox
     *
     * @param int $post_id
     * @return void
     */
    public function save_metabox($post_id){
        if (!isset($_POST['uts_metabox_nonce_field'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['uts_metabox_nonce_field'], 'uts_metabox_nonce')) {
            return;
        }

        if (isset($_POST['uts_designation'])) {
            update_post_meta($post_id, 'uts_designation', sanitize_text_field($_POST['uts_designation']));
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

Metabox::instance();