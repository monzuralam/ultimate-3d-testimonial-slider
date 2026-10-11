<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Register the REST routes.
 *
 * POST /uts/v1/preview renders [uts] with the given attributes, for the
 * shortcode playground on the Getting Started page.
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
if (!function_exists('uts_register_rest_routes')) {
    function uts_register_rest_routes() {
        register_rest_route('uts/v1', '/preview', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'uts_rest_preview',
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
            'args'                => array(
                'attributes' => array(
                    'type'    => 'object',
                    'default' => array(),
                ),
            ),
        ));
    }
    add_action('rest_api_init', 'uts_register_rest_routes');
}

/**
 * Convert playground values to shortcode attributes.
 *
 * Only known keys are kept; values become strings, as in a real shortcode.
 * The shortcode itself sanitizes them (uts_sanitize_settings()).
 *
 * @since 1.1.0
 * @author Monzur Alam
 *
 * @param array $attributes Raw attributes.
 * @return array
 */
if (!function_exists('uts_preview_attributes')) {
    function uts_preview_attributes($attributes) {
        $allowed = array_merge(array_keys(uts_settings_schema()), array('category'));
        $atts    = array();

        foreach ((array) $attributes as $key => $value) {
            if (!in_array($key, $allowed, true) || is_array($value) || is_object($value)) {
                continue;
            }
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $atts[$key] = sanitize_text_field((string) $value);
        }

        return $atts;
    }
}

/**
 * Preview endpoint callback.
 *
 * @since 1.1.0
 * @author Monzur Alam
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
if (!function_exists('uts_rest_preview')) {
    function uts_rest_preview($request) {
        $atts = uts_preview_attributes($request->get_param('attributes'));

        return rest_ensure_response(array(
            'html' => uts_shortcode($atts),
        ));
    }
}
