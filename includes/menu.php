<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Register the Getting Started page under Testimonials.
 *
 * @since 1.0.1
 * @author Monzur Alam
 */
if (!function_exists('uts_register_menu')) {
    function uts_register_menu() {
        add_submenu_page(
            'edit.php?post_type=uts',
            __('Getting Started', 'uts'),
            __('Getting Started', 'uts'),
            'edit_posts',
            'uts-getting-started',
            'uts_getting_started_callback'
        );
    }
    add_action('admin_menu', 'uts_register_menu');
}

/**
 * Shortcode examples shown on the Getting Started page.
 *
 * @since 1.1.0
 * @author Monzur Alam
 *
 * @return array Shortcode => description.
 */
if (!function_exists('uts_getting_started_examples')) {
    function uts_getting_started_examples() {
        return array(
            '[uts]'                                      => __('All testimonials, using the defaults from Settings.', 'uts'),
            '[uts type="grid"]'                          => __('Grid layout.', 'uts'),
            '[uts type="list" count="3"]'                => __('List of the first 3 testimonials.', 'uts'),
            '[uts category="clients"]'                   => __('Only testimonials in the "clients" category (use the category slug).', 'uts'),
            '[uts effect="fade" autoplay="true"]'        => __('Fading slider that plays automatically.', 'uts'),
            '[uts navigation="true" pagination="false"]' => __('Slider with arrows instead of dots.', 'uts'),
        );
    }
}

/**
 * Print one copyable shortcode field.
 *
 * Same markup as the shortcode metabox, so src/admin handles the copy button.
 *
 * @since 1.1.0
 * @author Monzur Alam
 *
 * @param string $shortcode Shortcode text.
 */
if (!function_exists('uts_copy_field')) {
    function uts_copy_field($shortcode) {
        ?>
        <div class="uts-shortcode-wrap">
            <input type="text" value="<?php echo esc_attr($shortcode); ?>" readonly />
            <button type="button" class="button uts-copy-shortcode" title="<?php echo esc_attr__('Copy', 'uts'); ?>">
                <span class="dashicons dashicons-admin-page"></span>
            </button>
        </div>
        <?php
    }
}

/**
 * Getting Started page callback.
 *
 * @since 1.0.1
 * @author Monzur Alam
 */
if (!function_exists('uts_getting_started_callback')) {
    function uts_getting_started_callback() {
        $count     = wp_count_posts('uts');
        $published = isset($count->publish) ? (int) $count->publish : 0;

        $steps = array(
            array(
                'title' => __('Add your testimonials', 'uts'),
                'text'  => __('Enter the client name as the title and their words as the content. Set a featured image for their photo, and fill in the position and company below the editor.', 'uts'),
                'url'   => admin_url('post-new.php?post_type=uts'),
                'link'  => __('Add Testimonial', 'uts'),
                'cap'   => 'edit_posts',
            ),
            array(
                'title' => __('Group them (optional)', 'uts'),
                'text'  => __('Use categories to show different testimonials on different pages, for example one set per service.', 'uts'),
                'url'   => admin_url('edit-tags.php?taxonomy=uts_category&post_type=uts'),
                'link'  => __('Manage Categories', 'uts'),
                'cap'   => 'manage_categories',
            ),
            array(
                'title' => __('Choose the look', 'uts'),
                'text'  => __('Pick the default layout, slider effect, autoplay, colors and more. These apply to every shortcode.', 'uts'),
                'url'   => admin_url('edit.php?post_type=uts&page=uts-settings'),
                'link'  => __('Open Settings', 'uts'),
                'cap'   => 'manage_options',
            ),
            array(
                'title' => __('Add the shortcode to a page', 'uts'),
                'text'  => __('Paste [uts] into a Shortcode block, a classic editor page or a widget. To change the order, set "Order" under Page Attributes on each testimonial.', 'uts'),
            ),
        );
        ?>
        <div class="wrap uts-getting-started">
            <h1><?php esc_html_e('Getting Started', 'uts'); ?></h1>
            <p class="uts-getting-started__intro">
                <?php
                printf(
                    /* translators: %s: number of published testimonials */
                    esc_html(_n('You have %s published testimonial.', 'You have %s published testimonials.', $published, 'uts')),
                    '<strong>' . esc_html(number_format_i18n($published)) . '</strong>'
                );
                echo ' ';
                esc_html_e('Show them anywhere as a 3D slider, grid or list in four steps.', 'uts');
                ?>
            </p>

            <ol class="uts-getting-started__steps">
                <?php foreach ($steps as $step) { ?>
                    <li class="uts-card">
                        <h2><?php echo esc_html($step['title']); ?></h2>
                        <p><?php echo esc_html($step['text']); ?></p>
                        <?php if (isset($step['url']) && current_user_can($step['cap'])) { ?>
                            <a class="button" href="<?php echo esc_url($step['url']); ?>"><?php echo esc_html($step['link']); ?></a>
                        <?php } ?>
                    </li>
                <?php } ?>
            </ol>

            <div class="uts-card">
                <h2><?php esc_html_e('Shortcode examples', 'uts'); ?></h2>
                <p><?php esc_html_e('Every option on the Settings page can also be set per shortcode, using the same name as an attribute. Attributes override the settings.', 'uts'); ?></p>
                <table class="widefat striped uts-getting-started__examples">
                    <tbody>
                        <?php foreach (uts_getting_started_examples() as $shortcode => $description) { ?>
                            <tr>
                                <td><?php uts_copy_field($shortcode); ?></td>
                                <td><?php echo esc_html($description); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <p>
                    <?php esc_html_e('In a theme template, use:', 'uts'); ?>
                    <code>&lt;?php echo do_shortcode( '[uts]' ); ?&gt;</code>
                </p>
            </div>
        </div>
        <?php
    }
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
