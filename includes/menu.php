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
add_action('admin_menu', 'uts_register_menu');

/**
 * Getting Started Callback
 */
function uts_getting_started_callback() {
?>
    <div class="wrap"></div>
<?php
}
