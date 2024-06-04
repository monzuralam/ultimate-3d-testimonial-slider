<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Register Menu
 */
function uts_register_menu() {
    add_submenu_page(
        'edit.php?post_type=uts',
        __('Shortcode Builder'),
        __('Shortcode Builder'),
        'manage_options',
        'shortcode-builder',
        'uts_shortcode_builder_callback'
    );
}
add_action('admin_menu', 'uts_register_menu');

/**
 * Shortcode Builder Callback
 */
function uts_shortcode_builder_callback() {
?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php echo esc_html__('Shortcode Builder', 'uts'); ?></h1>
        <a href="" class="page-title-action"><?php echo esc_html__('Add New Shortcode', 'uts'); ?></a>
        <hr class="wp-header-end">
        <div class="uts-shortcode-builder"></div>
        <div class="clear"></div>
    </div>
<?php
}
