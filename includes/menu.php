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
        __('Getting Started'),
        __('Getting Started'),
        'manage_options',
        'getting-started',
        'uts_getting_started_callback'
    );
}
// add_action('admin_menu', 'uts_register_menu');

/**
 * Getting Started Callback
 */
function uts_getting_started_callback() {
?>
    <div class="wrap"></div>
<?php
}

/**
 * Register the Settings page under Testimonials.
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
if (!function_exists('uts_register_settings_page')) {
    function uts_register_settings_page() {
        $hook = add_submenu_page(
            'edit.php?post_type=uts',
            __('Testimonial Settings', 'uts'),
            __('Settings', 'uts'),
            'manage_options',
            'uts-settings',
            'uts_settings_page_callback'
        );

        add_action('admin_print_scripts-' . $hook, 'uts_settings_page_assets');
    }
    add_action('admin_menu', 'uts_register_settings_page');
}

/**
 * Settings page callback: the React app mounts here.
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
if (!function_exists('uts_settings_page_callback')) {
    function uts_settings_page_callback() {
        echo '<div class="wrap"><div id="uts-settings-root"></div></div>';
    }
}

/**
 * Settings page assets.
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
if (!function_exists('uts_settings_page_assets')) {
    function uts_settings_page_assets() {
        if (!uts_register_build('uts-settings', 'settings')) {
            return;
        }

        wp_add_inline_script(
            'uts-settings',
            'window.utsSettingsData = ' . wp_json_encode(array(
                'schema'   => uts_settings_schema(),
                'defaults' => uts_default_settings(),
            )) . ';',
            'before'
        );

        wp_enqueue_style('wp-components');
        wp_enqueue_style('uts-settings');
        wp_enqueue_script('uts-settings');
        wp_set_script_translations('uts-settings', 'uts');
    }
}
