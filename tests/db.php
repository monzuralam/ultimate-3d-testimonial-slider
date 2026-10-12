<?php
/**
 * Tests for saved shortcode instances (todo.md section 4): the table, its
 * upgrade path, CRUD, sanitizing, caching and [uts id="…"] resolution.
 *
 * Run from the WordPress root:
 *
 *     wp eval-file wp-content/plugins/ultimate-3d-testimonial-slider/tests/db.php
 *
 * Exits 0 when every check passes, 1 otherwise. Rows it creates are deleted
 * at the end. The upgrade test drops and re-creates the (empty) table and
 * the `uts_db_version` option; testimonials and `uts_settings` are only read.
 *
 * @package UTS
 * @since 1.1.0
 * @author Monzur Alam
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

use UTS\DB;

$uts_failed  = 0;
$uts_created = array();

$check = function ( $label, $ok, $detail = '' ) use ( &$uts_failed ) {
	if ( ! $ok ) {
		$uts_failed++;
	}
	WP_CLI::line( ( $ok ? 'PASS ' : 'FAIL ' ) . $label . ( $ok || '' === $detail ? '' : ' -- ' . $detail ) );
};

$config = function ( $html ) {
	if ( ! preg_match( '/data-uts-config="([^"]*)"/', $html, $m ) ) {
		return null;
	}
	return json_decode( html_entity_decode( $m[1] ), true );
};

$items = function ( $html ) {
	return substr_count( $html, 'class="ucs-item' );
};

global $wpdb;
$table = DB::table();
$admin = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
wp_set_current_user( (int) $admin[0] );

try {
	// ------------------------------------------------------------------
	WP_CLI::line( '-- Upgrade from 1.0.1 (no table, no version option)' );
	$testimonials_before = (int) wp_count_posts( 'uts' )->publish;
	$settings_before     = get_option( 'uts_settings', 'missing' );
	$rows_before         = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore WordPress.DB

	if ( $rows_before > 0 ) {
		WP_CLI::warning( "{$table} has {$rows_before} row(s); skipping the drop/re-create part of the upgrade test." );
	} else {
		$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB
		delete_option( DB::VERSION_OPTION );
		$check( 'table gone before upgrade', $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table );

		$db = new DB();
		$db->maybe_upgrade();
		$check( 'maybe_upgrade() creates the table', $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table );
		$check( 'stores the schema version', DB::VERSION === get_option( DB::VERSION_OPTION ) );

		$wpdb->suppress_errors( false );
		$wpdb->last_error = '';
		DB::install();
		$check( 'install() is idempotent (no SQL error)', '' === $wpdb->last_error, $wpdb->last_error );
	}

	$columns = $wpdb->get_col( "SHOW COLUMNS FROM {$table}" ); // phpcs:ignore WordPress.DB
	$check( 'columns', array( 'id', 'title', 'settings', 'status', 'author_id', 'created_at', 'updated_at' ) === $columns, implode( ',', $columns ) );
	$check( 'testimonials untouched', $testimonials_before === (int) wp_count_posts( 'uts' )->publish );
	$check( 'uts_settings untouched', get_option( 'uts_settings', 'missing' ) === $settings_before );

	// ------------------------------------------------------------------
	WP_CLI::line( '-- Settings::sanitize() partial mode' );
	$full    = UTS\Settings::sanitize( array( 'effect' => 'fade' ) );
	$partial = UTS\Settings::sanitize( array( 'effect' => 'fade', 'speed' => '99999', 'autoplay' => 'false', 'nope' => 1 ), true );
	$check( 'default mode still fills every key', count( $full ) === count( UTS\Settings::schema() ) );
	$check( 'partial keeps only sent keys, sanitized', array( 'effect' => 'fade', 'autoplay' => false, 'speed' => 5000 ) == $partial, wp_json_encode( $partial ) ); // phpcs:ignore Universal.Operators.StrictComparisons
	$check( 'sanitize_option() (register_setting path) still full', count( sanitize_option( 'uts_settings', array( 'effect' => 'fade' ) ) ) === count( UTS\Settings::schema() ) );

	// ------------------------------------------------------------------
	WP_CLI::line( '-- Insert / get' );
	$category = get_terms( array( 'taxonomy' => 'uts_category', 'hide_empty' => true, 'number' => 1 ) );
	$slug     = $category ? $category[0]->slug : '';

	$id = DB::insert(
		array(
			'title'    => '  <b>Homepage</b> slider ',
			'settings' => array(
				'effect'   => 'fade',
				'autoplay' => 'true',
				'speed'    => '99999',
				'evil'     => '<script>',
				'category' => $slug ? $slug : 'no-such-category',
			),
		)
	);
	$uts_created[] = $id;
	$check( 'insert returns an ID', is_int( $id ) && $id > 0, is_wp_error( $id ) ? $id->get_error_message() : '' );

	$item = DB::get( $id );
	$expected_settings = array( 'effect' => 'fade', 'autoplay' => true, 'speed' => 5000 );
	if ( $slug ) {
		$expected_settings['category'] = $slug;
	}
	$check( 'title sanitized', 'Homepage slider' === $item['title'], $item['title'] );
	$check( 'only sent keys stored, clamped, unknown dropped', $expected_settings == $item['settings'], wp_json_encode( $item['settings'] ) ); // phpcs:ignore Universal.Operators.StrictComparisons
	$check( 'defaults: status publish, author = current user', 'publish' === $item['status'] && get_current_user_id() === $item['author_id'] );
	$check( 'GMT timestamps set', (bool) preg_match( '/^\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/', $item['created_at'] ) && $item['created_at'] === $item['updated_at'] );
	$check( 'unknown category dropped', ! isset( DB::get( $uts_created[] = DB::insert( array( 'title' => 'x', 'settings' => array( 'category' => 'no-such-category' ) ) ) )['settings']['category'] ) );

	$empty = DB::insert( array( 'title' => '   ' ) );
	$uts_created[] = $empty;
	$check( 'empty title becomes "UTS #{id}"', 'UTS #' . $empty === DB::get( $empty )['title'], DB::get( $empty )['title'] );
	$predicted = DB::next_id();
	$check( 'next_id() is above the highest ID', $predicted > max( array_filter( $uts_created, 'is_int' ) ) );
	DB::delete( $empty, true );
	$check( 'next_id() skips IDs of deleted rows', DB::next_id() === $predicted, DB::next_id() . ' vs ' . $predicted );
	$empty         = DB::insert( array( 'title' => '' ) );
	$uts_created[] = $empty;
	$check( 'next_id() matches the next insert', $predicted === $empty, $empty . ' vs ' . $predicted );
	$check( 'get() of unknown ID is null', null === DB::get( 999999999 ) && null === DB::get( 0 ) && null === DB::get( 'abc' ) );
	$check( 'uts_get_shortcode() wrapper', uts_get_shortcode( $id ) === DB::get( $id ) );

	// ------------------------------------------------------------------
	WP_CLI::line( '-- Cache' );
	$wpdb->update( $table, array( 'title' => 'Changed behind the cache' ), array( 'id' => $id ) ); // phpcs:ignore WordPress.DB
	$check( 'get() is served from the object cache', 'Homepage slider' === DB::get( $id )['title'] );
	DB::update( $id, array( 'title' => 'Homepage slider 2' ) );
	$check( 'update() invalidates the cache', 'Homepage slider 2' === DB::get( $id )['title'] );

	// ------------------------------------------------------------------
	WP_CLI::line( '-- Update' );
	$check( 'settings replaced, partial', true === DB::update( $id, array( 'settings' => array( 'effect' => 'cards' ) ) ) && array( 'effect' => 'cards' ) === DB::get( $id )['settings'] );
	DB::update( $id, array( 'settings' => $expected_settings ) );
	$bad_status = DB::update( $id, array( 'status' => 'nope' ) );
	$check( 'invalid status -> 400 error', is_wp_error( $bad_status ) && 400 === $bad_status->get_error_data()['status'] );
	$no_title = DB::update( $id, array( 'title' => ' ' ) );
	$check( 'emptied title on update becomes "UTS #{id}"', true === $no_title && 'UTS #' . $id === DB::get( $id )['title'], DB::get( $id )['title'] );
	DB::update( $id, array( 'title' => 'Homepage slider 2' ) ); // Later checks search for this name.
	$missing = DB::update( 999999999, array( 'title' => 'x' ) );
	$check( 'update unknown ID -> 404 error', is_wp_error( $missing ) && 404 === $missing->get_error_data()['status'] );

	// ------------------------------------------------------------------
	WP_CLI::line( '-- Duplicate' );
	$copy = DB::duplicate( $id );
	$uts_created[] = $copy;
	$copy_item = DB::get( $copy );
	$check( 'copy is a draft titled "… (copy)" with the same settings', 'draft' === $copy_item['status'] && 'Homepage slider 2 (copy)' === $copy_item['title'] && $copy_item['settings'] === DB::get( $id )['settings'] );

	// ------------------------------------------------------------------
	WP_CLI::line( '-- Query / count' );
	$found = DB::query( array( 'search' => 'Homepage slider 2', 'per_page' => 1 ) );
	$check( 'search + pagination', 2 === $found['total'] && 1 === count( $found['items'] ), wp_json_encode( array( $found['total'], count( $found['items'] ) ) ) );
	$drafts = DB::query( array( 'status' => 'draft', 'search' => 'Homepage slider 2' ) );
	$check( 'status filter', 1 === $drafts['total'] && $copy === $drafts['items'][0]['id'] );
	$injection = DB::query( array( 'orderby' => 'id; DROP TABLE x', 'order' => 'sideways', 'search' => "' OR 1=1 --" ) );
	$check( 'bad orderby/order/search are harmless', 0 === $injection['total'] && '' === $wpdb->last_error, $wpdb->last_error );
	$counts = DB::count();
	$check( 'count() has every status', array( 'publish', 'draft', 'trash' ) === array_keys( $counts ) );

	// ------------------------------------------------------------------
	WP_CLI::line( '-- [uts id] precedence' );
	$total = (int) wp_count_posts( 'uts' )->publish;
	wp_set_current_user( 0 );

	$html = do_shortcode( '[uts id="' . $id . '"]' );
	$cfg  = $config( $html );
	$check( 'instance options apply (effect, autoplay)', $cfg && 'fade' === $cfg['effect'] && false !== $cfg['autoplay'], wp_json_encode( $cfg ) );
	$check( 'global settings fill the rest (type slider)', false !== strpos( $html, 'ucs-container slider' ) );
	if ( $slug ) {
		$in_category = count( get_posts( array( 'post_type' => 'uts', 'posts_per_page' => -1, 'fields' => 'ids', 'tax_query' => array( array( 'taxonomy' => 'uts_category', 'field' => 'slug', 'terms' => $slug ) ) ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		$check( 'instance category filters testimonials', $in_category === $items( $html ), $in_category . ' vs ' . $items( $html ) );
	}

	$cfg = $config( do_shortcode( '[uts id="' . $id . '" autoplay="false"]' ) );
	$check( '[uts id autoplay="false"] overrides the instance', $cfg && false === $cfg['autoplay'] && 'fade' === $cfg['effect'], wp_json_encode( $cfg ) );

	$grid = do_shortcode( '[uts id="' . $id . '" type="grid"]' );
	$check( 'attribute type overrides', false !== strpos( $grid, 'ucs-container grid' ) && null === $config( $grid ) );

	$check( 'uts_shortcode() wrapper accepts id', $config( uts_shortcode( array( 'id' => (string) $id ) ) )['effect'] === 'fade' );
	$check( '[uts] without id unchanged', $total === $items( do_shortcode( '[uts]' ) ) );

	// ------------------------------------------------------------------
	WP_CLI::line( '-- Unknown / draft / trashed IDs' );
	$check( 'unknown id: nothing for visitors', '' === do_shortcode( '[uts id="999999999"]' ) );
	$check( 'draft id: nothing for visitors', '' === do_shortcode( '[uts id="' . $copy . '"]' ) );
	wp_set_current_user( (int) $admin[0] );
	$check( 'unknown id: HTML comment for editors', '<!-- uts: shortcode 999999999 not found -->' === do_shortcode( '[uts id="999999999"]' ) );
	$check( 'draft id: HTML comment for editors', '<!-- uts: shortcode ' . $copy . ' not found -->' === do_shortcode( '[uts id="' . $copy . '"]' ) );
	$check( 'non-numeric id is treated as unknown', '<!-- uts: shortcode 0 not found -->' === do_shortcode( '[uts id="abc"]' ) );

	// ------------------------------------------------------------------
	WP_CLI::line( '-- Delete' );
	$check( 'delete() trashes', true === DB::delete( $copy ) && 'trash' === DB::get( $copy )['status'] );
	$check( 'trashed items excluded from status any', 0 === count( wp_list_filter( DB::query( array( 'per_page' => 100 ) )['items'], array( 'id' => $copy ) ) ) );
	$check( 'trashed id renders nothing', '<!-- uts: shortcode ' . $copy . ' not found -->' === do_shortcode( '[uts id="' . $copy . '"]' ) );
	$check( 'delete( force ) removes the row', true === DB::delete( $copy, true ) && null === DB::get( $copy ) );
	$gone = DB::delete( $copy, true );
	$check( 'delete unknown ID -> 404 error', is_wp_error( $gone ) && 404 === $gone->get_error_data()['status'] );
} finally {
	foreach ( array_filter( $uts_created, 'is_int' ) as $created_id ) {
		if ( DB::get( $created_id ) ) {
			DB::delete( $created_id, true );
		}
	}
	$left = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore WordPress.DB
	WP_CLI::line( "-- Cleanup: {$left} row(s) left in {$table}" );
}

if ( $uts_failed ) {
	WP_CLI::error( "{$uts_failed} check(s) failed." );
}
WP_CLI::success( 'All checks passed.' );
