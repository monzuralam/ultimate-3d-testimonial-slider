<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * External links used in the admin pages.
 *
 * @since 1.1.0
 * @author Monzur Alam
 *
 * @param string $key docs|support|review|github
 * @return string
 */
if (!function_exists('uts_link')) {
    function uts_link($key) {
        $links = array(
            'docs'    => 'https://wordpress.org/plugins/ultimate-3d-testimonial-slider/',
            'support' => 'https://wordpress.org/support/plugin/ultimate-3d-testimonial-slider/',
            'review'  => 'https://wordpress.org/support/plugin/ultimate-3d-testimonial-slider/reviews/#new-post',
            'github'  => 'https://github.com/monzuralam/ultimate-3d-testimonial-slider',
        );

        return isset($links[$key]) ? $links[$key] : '';
    }
}

/**
 * Register the plugin's admin pages under Testimonials.
 *
 * @since 1.0.1
 * @author Monzur Alam
 */
if (!function_exists('uts_register_menu')) {
    function uts_register_menu() {
        $getting_started = add_submenu_page(
            'edit.php?post_type=uts',
            __('Getting Started', 'uts'),
            __('Getting Started', 'uts'),
            'edit_posts',
            'uts-getting-started',
            'uts_getting_started_callback'
        );

        $settings = add_submenu_page(
            'edit.php?post_type=uts',
            __('Testimonial Settings', 'uts'),
            __('Settings', 'uts'),
            'manage_options',
            'uts-settings',
            'uts_settings_page_callback'
        );

        add_action('load-' . $getting_started, 'uts_getting_started_load');
        add_action('load-' . $settings, 'uts_settings_page_load');
    }
    add_action('admin_menu', 'uts_register_menu');
}

/**
 * Shared header (plugin name, version and tabs) for the plugin's admin pages.
 *
 * The `.wp-header-end` marker makes WordPress put admin notices below it.
 *
 * @since 1.1.0
 * @author Monzur Alam
 *
 * @param string $active Active tab key.
 */
if (!function_exists('uts_admin_header')) {
    function uts_admin_header($active) {
        $tabs = array(
            'getting-started' => array(__('Getting Started', 'uts'), admin_url('edit.php?post_type=uts&page=uts-getting-started'), 'edit_posts'),
            'settings'        => array(__('Settings', 'uts'), admin_url('edit.php?post_type=uts&page=uts-settings'), 'manage_options'),
            'testimonials'    => array(__('All Testimonials', 'uts'), admin_url('edit.php?post_type=uts'), 'edit_posts'),
            'categories'      => array(__('Categories', 'uts'), admin_url('edit-tags.php?taxonomy=uts_category&post_type=uts'), 'manage_categories'),
        );
        ?>
        <div class="uts-admin-header uts:flex uts:flex-wrap uts:items-center uts:gap-x-8 uts:gap-y-1 uts:border-b uts:border-wp-border uts:bg-white uts:px-5 uts:md:px-8">
            <div class="uts:flex uts:items-center uts:gap-3 uts:py-3">
                <span class="dashicons dashicons-slides uts:text-wp-blue uts:text-[26px] uts:size-[26px]" aria-hidden="true"></span>
                <span class="uts:text-lg uts:font-semibold uts:text-gray-900"><?php esc_html_e('Ultimate 3D Testimonial Slider', 'uts'); ?></span>
                <span class="uts:rounded-full uts:border uts:border-wp-border uts:px-2 uts:py-0.5 uts:text-xs uts:text-wp-muted">v<?php echo esc_html(UTS_VERSION); ?></span>
            </div>
            <nav class="uts:flex uts:flex-1 uts:flex-wrap uts:gap-x-6" aria-label="<?php esc_attr_e('Plugin pages', 'uts'); ?>">
                <?php
                foreach ($tabs as $key => $tab) {
                    if (!current_user_can($tab[2])) {
                        continue;
                    }
                    $is_active = $key === $active;
                    ?>
                    <a href="<?php echo esc_url($tab[1]); ?>"
                        class="uts:-mb-px uts:border-b-2 uts:py-4 uts:text-sm uts:font-medium uts:no-underline uts:focus:shadow-none <?php echo $is_active ? 'uts:border-wp-blue uts:text-wp-blue' : 'uts:border-transparent uts:text-gray-700 uts:hover:text-wp-blue'; ?>"
                        <?php echo $is_active ? 'aria-current="page"' : ''; ?>>
                        <?php echo esc_html($tab[0]); ?>
                    </a>
                <?php } ?>
            </nav>
            <a href="<?php echo esc_url(uts_link('support')); ?>" target="_blank" rel="noopener noreferrer" class="uts:flex uts:items-center uts:gap-1 uts:py-3 uts:text-sm uts:font-medium uts:text-wp-blue uts:no-underline">
                <span class="dashicons dashicons-sos" aria-hidden="true"></span>
                <?php esc_html_e('Get Help', 'uts'); ?>
            </a>
        </div>
        <hr class="wp-header-end uts:hidden">
        <?php
    }
}

/**
 * Settings page: enqueue assets on load.
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
if (!function_exists('uts_settings_page_load')) {
    function uts_settings_page_load() {
        add_action('admin_enqueue_scripts', 'uts_settings_page_assets');
    }
}

/**
 * Settings page callback: the React app mounts here.
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
if (!function_exists('uts_settings_page_callback')) {
    function uts_settings_page_callback() {
        uts_admin_header('settings');
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
