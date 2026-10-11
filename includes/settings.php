<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Settings schema.
 *
 * Single source of truth for the `uts_settings` option: the REST schema,
 * the defaults and the sanitization are all derived from it.
 *
 * @since 1.1.0
 * @author Monzur Alam
 *
 * @return array JSON schema properties keyed by setting name.
 */
if (!function_exists('uts_settings_schema')) {
    function uts_settings_schema() {
        $schema = array(
            // General
            'type' => array(
                'type'    => 'string',
                'enum'    => array('slider', 'grid', 'list'),
                'default' => 'slider',
            ),
            'count' => array(
                'type'    => 'integer',
                'minimum' => 0,
                'maximum' => 100,
                'default' => 0, // 0 = all
            ),
            'excerpt_length' => array(
                'type'    => 'integer',
                'minimum' => 0,
                'maximum' => 500,
                'default' => 50, // 0 = full content
            ),
            'image_size' => array(
                'type'    => 'string',
                'enum'    => array('thumbnail', 'medium', 'large', 'full'),
                'default' => 'large',
            ),

            // Slider
            'effect' => array(
                'type'    => 'string',
                'enum'    => array('coverflow', 'slide', 'fade', 'cards'),
                'default' => 'coverflow',
            ),
            'autoplay' => array(
                'type'    => 'boolean',
                'default' => false,
            ),
            'autoplay_delay' => array(
                'type'    => 'integer',
                'minimum' => 1000,
                'maximum' => 20000,
                'default' => 3000,
            ),
            'loop' => array(
                'type'    => 'boolean',
                'default' => false,
            ),
            'speed' => array(
                'type'    => 'integer',
                'minimum' => 100,
                'maximum' => 5000,
                'default' => 300,
            ),
            'pagination' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'navigation' => array(
                'type'    => 'boolean',
                'default' => false,
            ),
            'coverflow_rotate' => array(
                'type'    => 'integer',
                'minimum' => 0,
                'maximum' => 90,
                'default' => 50,
            ),
            'coverflow_depth' => array(
                'type'    => 'integer',
                'minimum' => 0,
                'maximum' => 500,
                'default' => 100,
            ),
            'coverflow_stretch' => array(
                'type'    => 'integer',
                'minimum' => -100,
                'maximum' => 100,
                'default' => 0,
            ),

            // Display
            'show_image' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'show_title' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'show_designation' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'grid_columns' => array(
                'type'    => 'integer',
                'minimum' => 1,
                'maximum' => 6,
                'default' => 3,
            ),

            // Style (empty color = theme/plugin CSS default)
            'accent_color' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'text_color' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'background_color' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'border_radius' => array(
                'type'    => 'integer',
                'minimum' => 0,
                'maximum' => 50,
                'default' => 0,
            ),
        );

        return apply_filters('uts_settings_schema', $schema);
    }
}

/**
 * Default settings.
 *
 * @since 1.1.0
 * @author Monzur Alam
 *
 * @return array
 */
if (!function_exists('uts_default_settings')) {
    function uts_default_settings() {
        $defaults = array();

        foreach (uts_settings_schema() as $key => $property) {
            $defaults[$key] = $property['default'];
        }

        return $defaults;
    }
}

/**
 * Sanitize the settings option.
 *
 * Unknown keys are dropped, values are cast to their schema type and
 * clamped/validated; invalid values fall back to the default.
 *
 * @since 1.1.0
 * @author Monzur Alam
 *
 * @param mixed $value Raw option value.
 * @return array
 */
if (!function_exists('uts_sanitize_settings')) {
    function uts_sanitize_settings($value) {
        $value     = is_array($value) ? $value : array();
        $sanitized = array();

        foreach (uts_settings_schema() as $key => $property) {
            if (!array_key_exists($key, $value)) {
                $sanitized[$key] = $property['default'];
                continue;
            }

            $raw = $value[$key];

            switch ($property['type']) {
                case 'boolean':
                    $sanitized[$key] = (bool) $raw;
                    break;

                case 'integer':
                    $int = is_numeric($raw) ? (int) $raw : $property['default'];
                    if (isset($property['minimum'])) {
                        $int = max($property['minimum'], $int);
                    }
                    if (isset($property['maximum'])) {
                        $int = min($property['maximum'], $int);
                    }
                    $sanitized[$key] = $int;
                    break;

                default:
                    $string = sanitize_text_field((string) $raw);

                    if (isset($property['enum'])) {
                        $string = in_array($string, $property['enum'], true) ? $string : $property['default'];
                    } elseif ('_color' === substr($key, -6)) {
                        $string = (string) sanitize_hex_color($string);
                    }

                    $sanitized[$key] = $string;
            }
        }

        return $sanitized;
    }
}

/**
 * Register the settings option, exposed to the REST API
 * (/wp/v2/settings) for the React settings page.
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
if (!function_exists('uts_register_settings')) {
    function uts_register_settings() {
        register_setting('uts', 'uts_settings', array(
            'type'              => 'object',
            'description'       => __('Ultimate 3D Testimonial Slider settings', 'uts'),
            'default'           => uts_default_settings(),
            'sanitize_callback' => 'uts_sanitize_settings',
            'show_in_rest'      => array(
                'schema' => array(
                    'type'                 => 'object',
                    'properties'           => uts_settings_schema(),
                    'additionalProperties' => false,
                ),
            ),
        ));
    }
    add_action('init', 'uts_register_settings');
}

/**
 * Get the settings, merged over the defaults.
 *
 * @since 1.1.0
 * @author Monzur Alam
 *
 * @param string|null $key Optional setting key.
 * @return mixed All settings, or one value (null if the key is unknown).
 */
if (!function_exists('uts_get_settings')) {
    function uts_get_settings($key = null) {
        $settings = get_option('uts_settings', array());
        $settings = wp_parse_args(is_array($settings) ? $settings : array(), uts_default_settings());

        if (null === $key) {
            return $settings;
        }

        return isset($settings[$key]) ? $settings[$key] : null;
    }
}
