<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if( !function_exists('uts_custom_metabox')){
    function uts_custom_metabox() {
        // Define the screens (post types) and corresponding callbacks
        $meta_boxes = [
            'uts' => [
                'id'        => 'uts_custom_metabox',
                'title'     => __('Client Additional', 'ust'),
                'callback'  => 'uts_custom_metabox_cb',
                'context'   =>  'normal',
            ],
            'shortcode-builder' => [
                'id'        => 'uts_shortcode_metabox',
                'title'     => __('Shortcode', 'ust'),
                'callback'  => 'uts_shortcode_cb',
                'context'   =>  'side',
            ]
        ];
        
        // Register the meta boxes for each screen
        foreach ( $meta_boxes as $screen => $meta_box ) {
            add_meta_box(
                $meta_box['id'],          // Unique ID
                $meta_box['title'],       // Box title
                $meta_box['callback'],    // Content callback, must be of type callable
                $screen,                  // Post type
                $meta_box['context']      // Context: 'normal', 'advanced', or 'side'
            );
        }
    }
    add_action( 'add_meta_boxes', 'uts_custom_metabox' );
}

if( !function_exists('uts_custom_metabox_cb') ){
    function uts_custom_metabox_cb( $post ) {
        wp_nonce_field( 'uts_metabox_nonce', 'uts_metabox_nonce_field' );
    
        $designation = get_post_meta( $post->ID, 'uts_designation', true );
        ?>
        <label for="uts_designation"><?php _e('Position & Company Name','ust'); ?></label>
        <input type="text" name="uts_designation" id="uts_designation" class="regular-text" value="<?php echo esc_attr($designation); ?>" placeholder="<?php _e('CEO, Company Name') ?>">
        <?php
    }
}

/**
 * Shortcode Metabox Callback
 * @since 1.0.1
 * @author Monzur Alam
 */
if( !function_exists('uts_shortcode_cb') ){
    function uts_shortcode_cb( $post ){
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
}

if( !function_exists('uts_custom_metabox_save_postdata') ){
    function uts_custom_metabox_save_postdata( $post_id ) {
        if( !isset( $_POST['uts_metabox_nonce_field'])){
            return;
        }
    
        if( !wp_verify_nonce( $_POST['uts_metabox_nonce_field'], 'uts_metabox_nonce') ){
            return;
        }
    
        if( isset( $_POST['uts_designation'])){
            update_post_meta( $post_id, 'uts_designation', sanitize_text_field( $_POST['uts_designation']) );
        }
    }
    add_action( 'save_post', 'uts_custom_metabox_save_postdata' );
}