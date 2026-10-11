<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Add Photo and Order columns to the testimonials list.
 *
 * @since 1.1.0
 * @author Monzur Alam
 *
 * @param array $columns List table columns.
 * @return array
 */
if (!function_exists('uts_admin_columns')) {
    function uts_admin_columns($columns) {
        $new = array();

        foreach ($columns as $key => $label) {
            if ('title' === $key) {
                $new['uts_photo'] = __('Photo', 'uts');
            }
            $new[$key] = $label;
        }

        $date = isset($new['date']) ? $new['date'] : null;
        unset($new['date']);
        $new['uts_designation'] = __('Position & Company', 'uts');
        $new['uts_order']       = __('Order', 'uts');
        if (null !== $date) {
            $new['date'] = $date;
        }

        return $new;
    }
    add_filter('manage_uts_posts_columns', 'uts_admin_columns');
}

/**
 * Render the custom column cells.
 *
 * @since 1.1.0
 * @author Monzur Alam
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
if (!function_exists('uts_admin_column_content')) {
    function uts_admin_column_content($column, $post_id) {
        switch ($column) {
            case 'uts_photo':
                if (has_post_thumbnail($post_id)) {
                    echo get_the_post_thumbnail($post_id, array(40, 40), array('class' => 'uts-list-photo', 'alt' => ''));
                } else {
                    echo '<span class="uts-list-photo uts-list-photo--missing" title="' . esc_attr__('No photo', 'uts') . '"><span class="dashicons dashicons-format-quote" aria-hidden="true"></span><span class="screen-reader-text">' . esc_html__('No photo', 'uts') . '</span></span>';
                }
                break;

            case 'uts_designation':
                $designation = get_post_meta($post_id, 'uts_designation', true);
                echo '' !== $designation ? esc_html($designation) : '<span aria-hidden="true">—</span>';
                break;

            case 'uts_order':
                echo (int) get_post_field('menu_order', $post_id);
                break;
        }
    }
    add_action('manage_uts_posts_custom_column', 'uts_admin_column_content', 10, 2);
}

/**
 * Make the Order column sortable.
 *
 * @since 1.1.0
 * @author Monzur Alam
 *
 * @param array $columns Sortable columns.
 * @return array
 */
if (!function_exists('uts_sortable_columns')) {
    function uts_sortable_columns($columns) {
        $columns['uts_order'] = 'menu_order';
        return $columns;
    }
    add_filter('manage_edit-uts_sortable_columns', 'uts_sortable_columns');
}

/**
 * Number of published testimonials without a featured image.
 *
 * @since 1.1.0
 * @author Monzur Alam
 *
 * @return int
 */
if (!function_exists('uts_count_without_photo')) {
    function uts_count_without_photo() {
        $query = new WP_Query(array(
            'post_type'      => 'uts',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
                array(
                    'key'     => '_thumbnail_id',
                    'compare' => 'NOT EXISTS',
                ),
            ),
        ));

        return (int) $query->found_posts;
    }
}

/**
 * Add a "Without photo" view above the testimonials list.
 *
 * @since 1.1.0
 * @author Monzur Alam
 *
 * @param array $views List table views.
 * @return array
 */
if (!function_exists('uts_admin_views')) {
    function uts_admin_views($views) {
        $count = uts_count_without_photo();

        if ($count < 1) {
            return $views;
        }

        $current = isset($_GET['uts_photo']) && 'missing' === $_GET['uts_photo']; // phpcs:ignore WordPress.Security.NonceVerification

        // Only this view should look selected while it is active.
        if ($current) {
            foreach ($views as $key => $view) {
                $views[$key] = str_replace(array(' class="current"', ' aria-current="page"'), '', $view);
            }
        }

        $views['uts_photo_missing'] = sprintf(
            '<a href="%s"%s>%s <span class="count">(%s)</span></a>',
            esc_url(admin_url('edit.php?post_type=uts&post_status=publish&uts_photo=missing')),
            $current ? ' class="current" aria-current="page"' : '',
            esc_html__('Without photo', 'uts'),
            esc_html(number_format_i18n($count))
        );

        return $views;
    }
    add_filter('views_edit-uts', 'uts_admin_views');
}

/**
 * Filter the testimonials list to those without a photo.
 *
 * @since 1.1.0
 * @author Monzur Alam
 *
 * @param WP_Query $query Current query.
 */
if (!function_exists('uts_filter_without_photo')) {
    function uts_filter_without_photo($query) {
        if (!is_admin() || !$query->is_main_query() || 'uts' !== $query->get('post_type')) {
            return;
        }

        if (!isset($_GET['uts_photo']) || 'missing' !== $_GET['uts_photo']) { // phpcs:ignore WordPress.Security.NonceVerification
            return;
        }

        $query->set('meta_query', array(
            array(
                'key'     => '_thumbnail_id',
                'compare' => 'NOT EXISTS',
            ),
        ));
    }
    add_action('pre_get_posts', 'uts_filter_without_photo');
}
