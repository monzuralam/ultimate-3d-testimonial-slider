<?php
/**
 * Plugin settings (the `uts_settings` option).
 *
 * @package UTS
 * @since 1.1.0
 * @author Monzur Alam
 */

namespace UTS;

defined( 'ABSPATH' ) || exit;

/**
 * The `uts_settings` option.
 *
 * The schema is the single source of truth: the REST schema, the defaults
 * and the sanitization are all derived from it.
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
class Settings {

	/**
	 * Option: keep the plugin data on uninstall (true) or remove it (false).
	 *
	 * @var string
	 */
	const KEEP_DATA_OPTION = 'uts_keep_data';

	/**
	 * Hook the setting registration.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_setting' ) );
	}

	/**
	 * Settings schema.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return array JSON schema properties keyed by setting name.
	 */
	public static function schema() {
		$schema = array(
			// General.
			'type'              => array(
				'type'    => 'string',
				'enum'    => array( 'slider', 'grid', 'list' ),
				'default' => 'slider',
			),
			'count'             => array(
				'type'    => 'integer',
				'minimum' => 0,
				'maximum' => 100,
				'default' => 0, // 0 = all.
			),
			'excerpt_length'    => array(
				'type'    => 'integer',
				'minimum' => 0,
				'maximum' => 500,
				'default' => 50, // 0 = full content.
			),
			'image_size'        => array(
				'type'    => 'string',
				'enum'    => array( 'thumbnail', 'medium', 'large', 'full' ),
				'default' => 'large',
			),

			// Slider.
			'effect'            => array(
				'type'    => 'string',
				'enum'    => array( 'coverflow', 'slide', 'fade', 'cards' ),
				'default' => 'coverflow',
			),
			'autoplay'          => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'autoplay_delay'    => array(
				'type'    => 'integer',
				'minimum' => 1000,
				'maximum' => 20000,
				'default' => 3000,
			),
			'loop'              => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'speed'             => array(
				'type'    => 'integer',
				'minimum' => 100,
				'maximum' => 5000,
				'default' => 300,
			),
			'pagination'        => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'navigation'        => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'coverflow_rotate'  => array(
				'type'    => 'integer',
				'minimum' => 0,
				'maximum' => 90,
				'default' => 50,
			),
			'coverflow_depth'   => array(
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

			// Display.
			'show_image'        => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'show_title'        => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'show_designation'  => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'grid_columns'      => array(
				'type'    => 'integer',
				'minimum' => 1,
				'maximum' => 6,
				'default' => 3,
			),

			// Style (empty color = theme/plugin CSS default).
			'accent_color'      => array(
				'type'    => 'string',
				'default' => '',
			),
			'text_color'        => array(
				'type'    => 'string',
				'default' => '',
			),
			'background_color'  => array(
				'type'    => 'string',
				'default' => '',
			),
			'border_radius'     => array(
				'type'    => 'integer',
				'minimum' => 0,
				'maximum' => 50,
				'default' => 0,
			),
		);

		return apply_filters( 'uts_settings_schema', $schema );
	}

	/**
	 * Default settings.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return array
	 */
	public static function defaults() {
		$defaults = array();

		foreach ( self::schema() as $key => $property ) {
			$defaults[ $key ] = $property['default'];
		}

		return $defaults;
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
	 * @param mixed $value   Raw option value.
	 * @param bool  $partial Only return the keys present in $value, without
	 *                       filling in defaults (saved shortcode instances
	 *                       store only the options they change). Default false.
	 * @return array
	 */
	public static function sanitize( $value, $partial = false ) {
		$value     = is_array( $value ) ? $value : array();
		$sanitized = array();

		foreach ( self::schema() as $key => $property ) {
			if ( ! array_key_exists( $key, $value ) ) {
				if ( ! $partial ) {
					$sanitized[ $key ] = $property['default'];
				}
				continue;
			}

			$raw = $value[ $key ];

			switch ( $property['type'] ) {
				case 'boolean':
					// Handles 'false'/'0' strings from shortcode attributes.
					$sanitized[ $key ] = rest_sanitize_boolean( $raw );
					break;

				case 'integer':
					$int = is_numeric( $raw ) ? (int) $raw : $property['default'];
					if ( isset( $property['minimum'] ) ) {
						$int = max( $property['minimum'], $int );
					}
					if ( isset( $property['maximum'] ) ) {
						$int = min( $property['maximum'], $int );
					}
					$sanitized[ $key ] = $int;
					break;

				default:
					$string = sanitize_text_field( (string) $raw );

					if ( isset( $property['enum'] ) ) {
						$string = in_array( $string, $property['enum'], true ) ? $string : $property['default'];
					} elseif ( '_color' === substr( $key, -6 ) ) {
						$string = (string) sanitize_hex_color( $string );
					}

					$sanitized[ $key ] = $string;
			}
		}

		return $sanitized;
	}

	/**
	 * Register the settings option, exposed to the REST API
	 * (/wp/v2/settings) for the React settings page.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function register_setting() {
		\register_setting(
			'uts',
			'uts_settings',
			array(
				'type'              => 'object',
				'description'       => __( 'Ultimate 3D Testimonial Slider settings', 'uts' ),
				'default'           => self::defaults(),
				'sanitize_callback' => array( 'UTS\Settings', 'sanitize' ),
				'show_in_rest'      => array(
					'schema' => array(
						'type'                 => 'object',
						'properties'           => self::schema(),
						'additionalProperties' => false,
					),
				),
			)
		);

		// Separate from uts_settings on purpose: those keys are also shortcode
		// attributes and saved-slider options, this one isn't.
		\register_setting(
			'uts',
			self::KEEP_DATA_OPTION,
			array(
				'type'              => 'boolean',
				'description'       => __( 'Keep the plugin data (settings and saved sliders) when the plugin is deleted.', 'uts' ),
				'default'           => true,
				'sanitize_callback' => array( 'UTS\Settings', 'sanitize_keep_data' ),
				'show_in_rest'      => true,
			)
		);
	}

	/**
	 * Store `uts_keep_data` as '1' / '0'.
	 *
	 * A boolean false would be saved as '' by update_option(), which the REST
	 * API then rejects as "not a boolean" and returns as null on the next load
	 * (so the Settings page would show "keep" while uninstall removes data).
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param mixed $value Raw value.
	 * @return string '1' or '0'.
	 */
	public static function sanitize_keep_data( $value ) {
		return rest_sanitize_boolean( $value ) ? '1' : '0';
	}

	/**
	 * Whether uninstalling keeps the plugin data (default true).
	 *
	 * uninstall.php reads the option directly, since it runs without the plugin loaded.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return bool
	 */
	public static function keep_data() {
		return rest_sanitize_boolean( get_option( self::KEEP_DATA_OPTION, true ) );
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
	public static function get( $key = null ) {
		$settings = get_option( 'uts_settings', array() );
		$settings = wp_parse_args( is_array( $settings ) ? $settings : array(), self::defaults() );

		if ( null === $key ) {
			return $settings;
		}

		return isset( $settings[ $key ] ) ? $settings[ $key ] : null;
	}
}
