<?php
/**
 * Tests for uninstall.php: keep or remove the plugin data depending on the
 * `uts_keep_data` option (Settings → Advanced).
 *
 * Run from the WordPress root:
 *
 *     wp eval-file wp-content/plugins/ultimate-3d-testimonial-slider/tests/uninstall.php
 *
 * Exits 0 when every check passes, 1 otherwise. For each case it backs up the
 * plugin options and adds one saved slider, runs the uninstall routine, then
 * restores the options, re-creates the table and deletes the test slider.
 * Refuses to run if the saved sliders table has rows (they would be lost in
 * the "remove" case).
 *
 * @package UTS
 * @since 1.1.0
 * @author Monzur Alam
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

use UTS\DB;

global $wpdb;

$table   = DB::table();
$options = array( 'uts_settings', 'uts_activation_redirect', 'uts_db_version', 'uts_keep_data' );

if ( (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ) > 0 ) { // phpcs:ignore WordPress.DB
	WP_CLI::error( "{$table} has saved sliders; this test would delete them in the \"remove\" case. Run it on a site without saved sliders." );
}

// Load the routine once (it runs for the current site immediately), then
// call uts_uninstall_site() per case. Back up first: the include itself acts.
$backup = array();
foreach ( $options as $option ) {
	$backup[ $option ] = get_option( $option, '__missing__' );
}

$uts_failed = 0;
$check      = function ( $label, $ok ) use ( &$uts_failed ) {
	if ( ! $ok ) {
		$uts_failed++;
	}
	WP_CLI::line( ( $ok ? 'PASS ' : 'FAIL ' ) . $label );
};
$restore    = function () use ( $backup, $table, $wpdb ) {
	DB::install();
	$wpdb->query( "DELETE FROM {$table}" ); // phpcs:ignore WordPress.DB
	foreach ( $backup as $option => $value ) {
		if ( '__missing__' === $value ) {
			delete_option( $option );
		} else {
			update_option( $option, $value );
		}
	}
	wp_cache_flush();
};

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	define( 'WP_UNINSTALL_PLUGIN', 'ultimate-3d-testimonial-slider/ultimate-3d-testimonial-slider.php' );
}
include_once WP_PLUGIN_DIR . '/ultimate-3d-testimonial-slider/uninstall.php';
$restore();

$posts = (int) wp_count_posts( 'uts' )->publish;

// Case => value of uts_keep_data (null = option never saved), expected to keep?
$cases = array(
	'never saved (default)' => array( null, true ),
	'keep (saved on)'       => array( true, true ),
	'remove (saved off)'    => array( false, false ),
);

try {
	foreach ( $cases as $label => $case ) {
		list( $value, $keep ) = $case;
		WP_CLI::line( '-- ' . $label . ': expect ' . ( $keep ? 'keep' : 'remove' ) );

		update_option( 'uts_settings', array_merge( UTS\Settings::defaults(), array( 'effect' => 'fade' ) ) );
		$id = DB::insert( array( 'title' => 'Uninstall test slider' ) );
		update_option( 'uts_activation_redirect', true );
		if ( null === $value ) {
			delete_option( 'uts_keep_data' );
		} else {
			// Saved the way the Settings page saves it (sanitize callback).
			update_option( 'uts_keep_data', $value );
		}
		wp_cache_flush();

		uts_uninstall_site();
		wp_cache_flush();

		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
		$check( 'activation redirect flag removed', '__missing__' === get_option( 'uts_activation_redirect', '__missing__' ) );
		$check( 'saved sliders table ' . ( $keep ? 'kept' : 'dropped' ), $keep === $table_exists );
		if ( $keep ) {
			$check( 'saved slider still there', 1 === (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE id = %d", $id ) ) ); // phpcs:ignore WordPress.DB
		}
		$check( 'uts_settings ' . ( $keep ? 'kept' : 'deleted' ), $keep === ( '__missing__' !== get_option( 'uts_settings', '__missing__' ) ) );
		$check( 'uts_db_version ' . ( $keep ? 'kept' : 'deleted' ), $keep === ( '__missing__' !== get_option( 'uts_db_version', '__missing__' ) ) );
		if ( ! $keep ) {
			$check( 'uts_keep_data deleted', '__missing__' === get_option( 'uts_keep_data', '__missing__' ) );
		}
		$check( 'testimonial posts kept', $posts === (int) wp_count_posts( 'uts' )->publish );

		$restore();
	}
} finally {
	$restore();
	WP_CLI::line( '-- Restored: ' . (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ) . " row(s) in {$table}, options as before" ); // phpcs:ignore WordPress.DB
}

if ( $uts_failed ) {
	WP_CLI::error( "{$uts_failed} check(s) failed." );
}
WP_CLI::success( 'All checks passed.' );
