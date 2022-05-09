<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if( !function_exists('ust_custom_metabox')){
    function ust_custom_metabox() {
        $screens = [ 'ust'];
        foreach ( $screens as $screen ) {
            add_meta_box(
                'ust_custom_metabox',                 // Unique ID
                __('Client Additional','ust'),      // Box title
                'ust_custom_metabox_cb',            // Content callback, must be of type callable
                $screen                            // Post type
            );
        }
    }
    add_action( 'add_meta_boxes', 'ust_custom_metabox' );
}

if( !function_exists('ust_custom_metabox_cb') ){
    function ust_custom_metabox_cb( $post ) {
        wp_nonce_field( 'ust_metabox_nonce', 'ust_metabox_nonce_field' );
    
        $designation = get_post_meta( $post->ID, 'ust_designation', true );
        ?>
        <label for="ust_designation"><?php _e('Position & Company Name','ust'); ?></label>
        <input type="text" name="ust_designation" id="ust_designation" class="regular-text" value="<?php echo esc_attr($designation); ?>" placeholder="<?php _e('CEO, Company Name') ?>">
        <?php
    }
}

if( !function_exists('ust_custom_metabox_save_postdata') ){
    function ust_custom_metabox_save_postdata( $post_id ) {
        if( !isset( $_POST['ust_metabox_nonce_field'])){
            return;
        }
    
        if( !wp_verify_nonce( $_POST['ust_metabox_nonce_field'], 'ust_metabox_nonce') ){
            return;
        }
    
        if( isset( $_POST['ust_designation'])){
            update_post_meta( $post_id, 'ust_designation', sanitize_text_field( $_POST['ust_designation']) );
        }
    }
    add_action( 'save_post', 'ust_custom_metabox_save_postdata' );
}