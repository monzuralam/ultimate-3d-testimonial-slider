<?php
/**
 * Plugin bootstrap.
 *
 * @package UTS
 * @since 1.1.0
 * @author Monzur Alam
 */

namespace UTS;

defined( 'ABSPATH' ) || exit;

/**
 * Bootstraps the plugin: creates each component once and calls its
 * register() method, and holds the activation/deactivation callbacks.
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
class Plugin {

	/**
	 * Shared instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Whether boot() has run.
	 *
	 * @var bool
	 */
	private $booted = false;

	/**
	 * Component instances, keyed by class name.
	 *
	 * @var array
	 */
	private $components = array();

	/**
	 * Get the shared instance.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Use Plugin::instance(). Adds no hooks.
	 */
	private function __construct() {}

	/**
	 * Create each component once and call its register() method.
	 *
	 * Safe to call more than once: later calls do nothing.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function boot() {
		if ( $this->booted ) {
			return;
		}

		$this->booted = true;

		foreach ( $this->component_classes() as $class ) {
			$component = new $class();
			$component->register();
			$this->components[ $class ] = $component;
		}
	}

	/**
	 * Component classes, in load order.
	 *
	 * Each class must have a public register() method holding all its
	 * add_action()/add_filter() calls. Add a class here when its
	 * includes/ file is converted (see the file mapping in todo.md, section 3),
	 * e.g. 'UTS\CPT', 'UTS\Assets', 'UTS\Settings', ...
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return string[] Fully qualified class names.
	 */
	private function component_classes() {
		return array(
			'UTS\DB',
			'UTS\CPT',
			'UTS\Assets',
			'UTS\Settings',
			'UTS\Shortcode',
			'UTS\Metabox',
			'UTS\Admin_Columns',
			'UTS\Getting_Started',
			'UTS\Menu',
		);
	}

	/**
	 * Get a booted component, e.g. to use its methods as callbacks or to
	 * remove one of its hooks:
	 * remove_action( 'admin_init', array( UTS\Plugin::instance()->component( 'UTS\Menu' ), 'activation_redirect' ) );
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param string $class Fully qualified class name.
	 * @return object|null The component, or null if it isn't booted.
	 */
	public function component( $class ) {
		return isset( $this->components[ $class ] ) ? $this->components[ $class ] : null;
	}

	/**
	 * Activation: register the post type, flush rewrite rules and
	 * open Getting Started once (see Menu::activation_redirect()).
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public static function activate() {
		// Register the custom post type so its rewrite rules are included in the flush.
		$cpt = new CPT();
		$cpt->register_types();
		// Clear the permalinks after the post type has been registered.
		flush_rewrite_rules();
		// Create the saved shortcodes table. Other sites on a network get theirs
		// on their next load (DB::maybe_upgrade()).
		DB::install();
		// Show Getting Started once, on the next admin page load.
		add_option( 'uts_activation_redirect', true );
	}

	/**
	 * Deactivation: unregister the post type and flush rewrite rules.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public static function deactivate() {
		// Unregister the post type, so the rules are no longer in memory.
		unregister_post_type( 'uts' );
		// Clear the permalinks to remove our post type's rules from the database.
		flush_rewrite_rules();
	}
}
