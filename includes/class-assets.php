<?php
/**
 * Frontend and admin assets.
 *
 * @package UTS
 * @since 1.1.0
 * @author Monzur Alam
 */

namespace UTS;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and conditionally enqueues the compiled build/ entries.
 *
 * Frontend assets load only where [uts] is used; admin assets only on the
 * plugin's own screens.
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
class Assets {

	/**
	 * Hook the asset loaders.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function register() {
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin' ) );
	}

	/**
	 * Provide the `react-jsx-runtime` script on WordPress < 6.6.
	 *
	 * The wordpress/scripts package compiles JSX for the automatic runtime,
	 * so React builds depend on `react-jsx-runtime`, which core only
	 * registers since 6.6. Without it WordPress skips the script entirely.
	 * This registers a tiny stand-in that maps jsx()/jsxs() onto
	 * wp.element.createElement (available since 5.0).
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public static function register_jsx_runtime_fallback() {
		if ( wp_script_is( 'react-jsx-runtime', 'registered' ) ) {
			return;
		}

		wp_register_script( 'react-jsx-runtime', false, array( 'wp-element' ), UTS_VERSION, true );
		wp_add_inline_script(
			'react-jsx-runtime',
			'(function (el) {' .
				'function jsx(type, props, key) {' .
					'var config = {};' .
					'for (var name in props) { if (Object.prototype.hasOwnProperty.call(props, name)) { config[name] = props[name]; } }' .
					'if (key !== undefined) { config.key = key; }' .
					'return el.createElement(type, config);' .
				'}' .
				'window.ReactJSXRuntime = { jsx: jsx, jsxs: jsx, Fragment: el.Fragment };' .
			'})(window.wp.element);'
		);
	}

	/**
	 * Enqueue the frontend assets.
	 *
	 * Called from the shortcode, so it also works for shortcodes in widgets
	 * or page builders that the early check in frontend() can't see.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public static function enqueue_frontend() {
		wp_enqueue_style( 'uts' );
		wp_enqueue_script( 'uts' );
	}

	/**
	 * Register frontend assets, and enqueue them early when the current
	 * post contains the shortcode (so the CSS loads in the head).
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function frontend() {
		uts_register_build( 'uts', 'frontend' );

		$post = get_post();

		if ( is_singular() && $post && has_shortcode( $post->post_content, 'uts' ) ) {
			self::enqueue_frontend();
		}
	}

	/**
	 * Admin assets, loaded only on the plugin's own screens.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function admin() {
		$screen = get_current_screen();

		if ( ! $screen || 'uts' !== $screen->post_type ) {
			return;
		}

		if ( uts_register_build( 'uts-admin', 'admin' ) ) {
			wp_enqueue_style( 'uts-admin' );
			wp_enqueue_script( 'uts-admin' );
		}
	}
}
