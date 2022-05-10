<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if( !function_exists('uts_custom_metabox')){
    function uts_custom_metabox() {
        $screens = [ 'uts'];
        foreach ( $screens as $screen ) {
            add_meta_box(
                'uts_custom_metabox',                 // Unique ID
                __('Client Additional','ust'),      // Box title
                'uts_custom_metabox_cb',            // Content callback, must be of type callable
                $screen                            // Post type
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