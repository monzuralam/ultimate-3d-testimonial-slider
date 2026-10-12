<?php
/**
 * Tests for the REST API (todo.md section 5): permissions per role,
 * validation errors, CRUD, preview and status.
 *
 * Run from the WordPress root:
 *
 *     wp eval-file wp-content/plugins/ultimate-3d-testimonial-slider/tests/rest.php
 *
 * Exits 0 when every check passes, 1 otherwise. Rows it creates are deleted
 * at the end. No users are created: an "editor" is simulated by giving the
 * first subscriber `edit_posts` through a `user_has_cap` filter.
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

/**
 * Send a REST request like apiFetch does (JSON body).
 */
$request = function ( $method, $route, $params = array() ) {
	$req = new WP_REST_Request( $method, '/uts/v1' . $route );
	if ( 'GET' === $method || 'DELETE' === $method ) {
		$req->set_query_params( $params );
	} else {
		$req->set_header( 'Content-Type', 'application/json' );
		$req->set_body( wp_json_encode( $params ) );
	}
	return rest_do_request( $req );
};

$status = function ( $response ) {
	return $response->get_status();
};

$error_code = function ( $response ) {
	$data = $response->get_data();
	return is_array( $data ) && isset( $data['code'] ) ? $data['code'] : '';
};

$admins      = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
$subscribers = get_users( array( 'role' => 'subscriber', 'number' => 1, 'fields' => 'ID' ) );
$admin       = (int) $admins[0];
$subscriber  = $subscribers ? (int) $subscribers[0] : 0;

$as_editor = function ( $allcaps ) {
	$allcaps['edit_posts'] = true;
	return $allcaps;
};

try {
	// A fixture to address by ID in the permission matrix.
	wp_set_current_user( $admin );
	$fixture       = DB::insert( array( 'title' => 'REST fixture' ) );
	$uts_created[] = $fixture;

	$routes = array(
		'GET /shortcodes'            => array( 'GET', '/shortcodes', array(), 'manage' ),
		'POST /shortcodes'           => array( 'POST', '/shortcodes', array( 'title' => 'x' ), 'manage' ),
		'GET /shortcodes/{id}'       => array( 'GET', '/shortcodes/' . $fixture, array(), 'manage' ),
		'PATCH /shortcodes/{id}'     => array( 'PATCH', '/shortcodes/' . $fixture, array( 'title' => 'REST fixture' ), 'manage' ),
		'DELETE /shortcodes/{id}'    => array( 'DELETE', '/shortcodes/999999999', array(), 'manage' ),
		'POST /shortcodes/{id}/dup'  => array( 'POST', '/shortcodes/999999999/duplicate', array(), 'manage' ),
		'GET /shortcodes/{id}/usage' => array( 'GET', '/shortcodes/' . $fixture . '/usage', array(), 'manage' ),
		'POST /preview'              => array( 'POST', '/preview', array( 'attributes' => array( 'type' => 'list' ) ), 'edit' ),
		'GET /status'                => array( 'GET', '/status', array(), 'edit' ),
	);

	// ------------------------------------------------------------------
	WP_CLI::line( '-- Permissions' );
	$roles = array( 'visitor' => 0 );
	if ( $subscriber ) {
		$roles['subscriber'] = $subscriber;
		$roles['editor (simulated)'] = $subscriber;
	} else {
		WP_CLI::warning( 'No subscriber user: subscriber/editor permission checks skipped.' );
	}
	$roles['administrator'] = $admin;

	foreach ( $roles as $role => $user_id ) {
		wp_set_current_user( $user_id );
		if ( 'editor (simulated)' === $role ) {
			add_filter( 'user_has_cap', $as_editor );
			wp_set_current_user( $user_id ); // Refresh caps.
		}

		foreach ( $routes as $label => $route ) {
			$code = $status( $request( $route[0], $route[1], $route[2] ) );
			if ( 'visitor' === $role ) {
				$want = 401;
			} elseif ( 'subscriber' === $role ) {
				$want = 403;
			} elseif ( 'editor (simulated)' === $role ) {
				$want = 'edit' === $route[3] ? 200 : 403;
			} else {
				$want = null; // Administrator: anything but 401/403.
			}
			$ok = null === $want ? ! in_array( $code, array( 401, 403 ), true ) : $want === $code;
			$check( str_pad( $role, 20 ) . $label, $ok, 'got ' . $code . ( null === $want ? '' : ', want ' . $want ) );
		}

		if ( 'editor (simulated)' === $role ) {
			add_filter( 'uts_builder_capability', function () { return 'edit_posts'; } ); // phpcs:ignore
			$check( str_pad( $role, 20 ) . 'GET /shortcodes with uts_builder_capability = edit_posts', 200 === $status( $request( 'GET', '/shortcodes' ) ) );
			remove_all_filters( 'uts_builder_capability' );
			remove_filter( 'user_has_cap', $as_editor );
		}
	}
	// The permission matrix as admin created one row ("x").
	foreach ( DB::query( array( 'search' => 'x', 'status' => 'any', 'per_page' => 100 ) )['items'] as $row ) {
		if ( 'x' === $row['title'] ) {
			$uts_created[] = $row['id'];
		}
	}

	wp_set_current_user( $admin );

	// ------------------------------------------------------------------
	WP_CLI::line( '-- Validation (400) and not found (404)' );
	$bad = array(
		'create with status trash'      => array( 'POST', '/shortcodes', array( 'title' => 'v', 'status' => 'trash' ) ),
		'unknown setting key'           => array( 'POST', '/shortcodes', array( 'title' => 'v', 'settings' => array( 'nope' => 1 ) ) ),
		'setting out of range'          => array( 'POST', '/shortcodes', array( 'title' => 'v', 'settings' => array( 'speed' => 99999 ) ) ),
		'setting not in enum'           => array( 'POST', '/shortcodes', array( 'title' => 'v', 'settings' => array( 'effect' => 'bogus' ) ) ),
		'setting wrong type'            => array( 'POST', '/shortcodes', array( 'title' => 'v', 'settings' => array( 'autoplay' => 'maybe' ) ) ),
		'title longer than 191'         => array( 'POST', '/shortcodes', array( 'title' => str_repeat( 'a', 192 ) ) ),
		'list per_page 101'             => array( 'GET', '/shortcodes', array( 'per_page' => 101 ) ),
		'list unknown status'           => array( 'GET', '/shortcodes', array( 'status' => 'bogus' ) ),
		'list unknown orderby'          => array( 'GET', '/shortcodes', array( 'orderby' => 'id; DROP TABLE x' ) ),
		'update with empty title'       => array( 'PATCH', '/shortcodes/' . $fixture, array( 'title' => ' ' ) ),
		'update with unknown status'    => array( 'PATCH', '/shortcodes/' . $fixture, array( 'status' => 'nope' ) ),
	);
	foreach ( $bad as $label => $route ) {
		$code = $status( $request( $route[0], $route[1], $route[2] ) );
		$check( '400 ' . $label, 400 === $code, 'got ' . $code );
	}
	foreach ( array( 'GET', 'PATCH', 'DELETE' ) as $method ) {
		$response = $request( $method, '/shortcodes/999999999', 'PATCH' === $method ? array( 'title' => 'x' ) : array() );
		$check( '404 ' . $method . ' unknown ID', 404 === $status( $response ) && 'uts_not_found' === $error_code( $response ), $status( $response ) . ' ' . $error_code( $response ) );
	}
	$check( '404 duplicate unknown ID', 404 === $status( $request( 'POST', '/shortcodes/999999999/duplicate' ) ) );
	$check( 'nothing was stored by the rejected requests', 0 === DB::query( array( 'search' => 'v', 'status' => 'any' ) )['total'] || ! in_array( 'v', wp_list_pluck( DB::query( array( 'search' => 'v', 'status' => 'any', 'per_page' => 100 ) )['items'], 'title' ), true ) );

	// ------------------------------------------------------------------
	WP_CLI::line( '-- Create / read / update' );
	$slug = '';
	$terms = get_terms( array( 'taxonomy' => 'uts_category', 'hide_empty' => true, 'number' => 1 ) );
	if ( $terms ) {
		$slug = $terms[0]->slug;
	}

	$created = $request(
		'POST',
		'/shortcodes',
		array(
			'title'    => 'REST slider',
			'status'   => 'draft',
			'settings' => array_filter(
				array(
					'effect'   => 'fade',
					'autoplay' => true,
					'category' => $slug,
				)
			),
		)
	);
	$item          = $created->get_data();
	$uts_created[] = isset( $item['id'] ) ? (int) $item['id'] : 0;
	$check( 'POST -> 201', 201 === $status( $created ), $status( $created ) . ' ' . wp_json_encode( $item ) );
	$check(
		'response shape',
		array( 'id', 'title', 'settings', 'status', 'shortcode', 'created_at', 'updated_at', 'author' ) === array_keys( $item ),
		implode( ',', array_keys( $item ) )
	);
	$check( 'shortcode string', '[uts id="' . $item['id'] . '"]' === $item['shortcode'] );
	$check( 'settings stored as sent', array_filter( array( 'effect' => 'fade', 'autoplay' => true, 'category' => $slug ) ) == (array) $item['settings'], wp_json_encode( $item['settings'] ) ); // phpcs:ignore Universal.Operators.StrictComparisons
	$check( 'status draft, author = admin', 'draft' === $item['status'] && $admin === $item['author']['id'] && '' !== $item['author']['name'] );
	$check( 'RFC 3339 dates', (bool) preg_match( '/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d/', $item['updated_at'] ) );
	$check( 'settings is a JSON object even when empty', '{}' === wp_json_encode( $request( 'GET', '/shortcodes/' . $fixture )->get_data()['settings'] ) );

	$id  = (int) $item['id'];
	$got = $request( 'GET', '/shortcodes/' . $id )->get_data();
	$check( 'GET returns the same item', $got == $item ); // phpcs:ignore Universal.Operators.StrictComparisons

	$patched = $request( 'PATCH', '/shortcodes/' . $id, array( 'status' => 'publish', 'settings' => array( 'effect' => 'cards' ) ) );
	$data    = $patched->get_data();
	$check( 'PATCH -> 200, settings replaced, title kept', 200 === $status( $patched ) && array( 'effect' => 'cards' ) === (array) $data['settings'] && 'REST slider' === $data['title'] && 'publish' === $data['status'], wp_json_encode( $data ) );
	$put = $request( 'PUT', '/shortcodes/' . $id, array( 'title' => 'REST slider renamed' ) );
	$check( 'PUT works too', 200 === $status( $put ) && 'REST slider renamed' === $put->get_data()['title'] );

	// ------------------------------------------------------------------
	WP_CLI::line( '-- List' );
	$list = $request( 'GET', '/shortcodes', array( 'search' => 'REST', 'per_page' => 1 ) );
	$headers = $list->get_headers();
	$check( 'X-WP-Total / X-WP-TotalPages', '2' === $headers['X-WP-Total'] && '2' === $headers['X-WP-TotalPages'], wp_json_encode( $headers ) );
	$check( 'per_page respected', 1 === count( $list->get_data() ) );
	$check( 'orderby title asc', array( 'REST fixture', 'REST slider renamed' ) === wp_list_pluck( $request( 'GET', '/shortcodes', array( 'search' => 'REST', 'orderby' => 'title', 'order' => 'asc' ) )->get_data(), 'title' ) );

	// ------------------------------------------------------------------
	WP_CLI::line( '-- Duplicate / preview' );
	$dup = $request( 'POST', '/shortcodes/' . $id . '/duplicate' );
	$dup_item      = $dup->get_data();
	$uts_created[] = (int) $dup_item['id'];
	$check( 'duplicate -> 201 draft copy', 201 === $status( $dup ) && 'draft' === $dup_item['status'] && 'REST slider renamed (copy)' === $dup_item['title'] );

	$html = $request( 'POST', '/preview', array( 'attributes' => array( 'id' => $id ) ) )->get_data()['html'];
	$check( 'preview of a published instance', false !== strpos( $html, 'uts-effect-cards' ), substr( $html, 0, 120 ) );
	$html = $request( 'POST', '/preview', array( 'attributes' => array( 'id' => $dup_item['id'], 'type' => 'grid' ) ) )->get_data()['html'];
	$check( 'preview renders a draft instance (+ attribute override)', false !== strpos( $html, 'ucs-container grid' ), substr( $html, 0, 120 ) );
	wp_set_current_user( 0 );
	$check( 'frontend still hides the draft after preview', '' === do_shortcode( '[uts id="' . $dup_item['id'] . '"]' ) );
	wp_set_current_user( $admin );
	$html = $request( 'POST', '/preview', array( 'attributes' => array( 'type' => 'list', 'evil' => '<x>', 'count' => array( 1 ), 'loop' => true ) ) )->get_data()['html'];
	$check( 'preview ignores unknown/array values, accepts booleans', false !== strpos( $html, 'ucs-container list' ) && false === strpos( $html, 'evil' ) );
	$check( 'preview_attributes()', array( 'type' => 'grid', 'loop' => 'false', 'count' => '3', 'id' => '7' ) === UTS\REST::preview_attributes( array( 'type' => 'grid', 'loop' => false, 'count' => 3, 'id' => 7, 'x' => 1, 'speed' => null ) ) );

	// ------------------------------------------------------------------
	WP_CLI::line( '-- Status' );
	$st = $request( 'GET', '/status' )->get_data();
	$check( 'status keys', array( 'published', 'with_photo', 'settings_saved', 'page_id', 'shortcodes' ) === array_keys( $st ), implode( ',', array_keys( $st ) ) );
	$check( 'status counts builder instances', isset( $st['shortcodes']['draft'] ) && $st['shortcodes']['draft'] >= 1 );

	// ------------------------------------------------------------------
	WP_CLI::line( '-- Usage ("Where is this used?")' );
	$check( 'usage of an unused slider is empty', array() === $request( 'GET', '/shortcodes/' . $id . '/usage' )->get_data() );
	$check( '404 usage of unknown ID', 404 === $status( $request( 'GET', '/shortcodes/999999999/usage' ) ) );
	$uts_posts   = array();
	$uts_posts[] = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'UTS usage test (quoted)', 'post_content' => 'Hello [uts id="' . $id . '" autoplay="false"] world' ) );
	$uts_posts[] = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'draft', 'post_title' => 'UTS usage test (bare)', 'post_content' => '[uts type="grid" id=' . $id . ']' ) );
	$uts_posts[] = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'UTS usage test (other id)', 'post_content' => '[uts id="' . $id . '0"] [uts category_id="' . $id . '"]' ) );
	$used        = $request( 'GET', '/shortcodes/' . $id . '/usage' )->get_data();
	$titles      = wp_list_pluck( $used, 'title' );
	sort( $titles );
	$check( 'usage finds quoted and unquoted ids, not other ids', array( 'UTS usage test (bare)', 'UTS usage test (quoted)' ) === $titles, wp_json_encode( $titles ) );
	$check( 'usage item shape', $used && array( 'id', 'title', 'type', 'status', 'edit_link', 'link' ) === array_keys( $used[0] ) && 'draft' === $used[0]['status'] );
	foreach ( $uts_posts as $uts_post ) {
		wp_delete_post( $uts_post, true );
	}
	$check( 'usage empty again after the posts are deleted', array() === $request( 'GET', '/shortcodes/' . $id . '/usage' )->get_data() );

	// ------------------------------------------------------------------
	WP_CLI::line( '-- uts_keep_data (uninstall choice) via /wp/v2/settings' );
	$keep_before = get_option( 'uts_keep_data', '__missing__' );
	$fresh_keep  = function () {
		// Read like a new page load: drop the cached option first.
		wp_cache_delete( 'uts_keep_data', 'options' );
		wp_cache_delete( 'alloptions', 'options' );
		$settings = rest_do_request( new WP_REST_Request( 'GET', '/wp/v2/settings' ) )->get_data();
		return isset( $settings['uts_keep_data'] ) ? $settings['uts_keep_data'] : 'missing';
	};
	delete_option( 'uts_keep_data' );
	$check( 'default (never saved) reads true', true === $fresh_keep() && true === UTS\Settings::keep_data() );
	foreach ( array( false, true ) as $value ) {
		$req = new WP_REST_Request( 'POST', '/wp/v2/settings' );
		$req->set_body_params( array( 'uts_keep_data' => $value ) );
		$code = rest_do_request( $req )->get_status();
		$read = $fresh_keep();
		$check( 'save ' . var_export( $value, true ) . ' -> stored as ' . var_export( get_option( 'uts_keep_data' ), true ) . ', fresh read ' . var_export( $read, true ), 200 === $code && $value === $read && $value === UTS\Settings::keep_data() && $value === rest_sanitize_boolean( get_option( 'uts_keep_data', true ) ) );
	}
	$req = new WP_REST_Request( 'POST', '/wp/v2/settings' );
	$req->set_body_params( array( 'uts_keep_data' => 'yes' ) );
	$check( 'invalid value -> 400', 400 === rest_do_request( $req )->get_status() );
	if ( '__missing__' === $keep_before ) {
		delete_option( 'uts_keep_data' );
	} else {
		update_option( 'uts_keep_data', $keep_before );
	}

	// ------------------------------------------------------------------
	WP_CLI::line( '-- Delete' );
	$trashed = $request( 'DELETE', '/shortcodes/' . $dup_item['id'] );
	$check( 'DELETE -> trashed item', 200 === $status( $trashed ) && 'trash' === $trashed->get_data()['status'] );
	$check( 'list (any) excludes trash, list (trash) has it', ! in_array( $dup_item['id'], wp_list_pluck( $request( 'GET', '/shortcodes', array( 'per_page' => 100 ) )->get_data(), 'id' ), true ) && in_array( $dup_item['id'], wp_list_pluck( $request( 'GET', '/shortcodes', array( 'status' => 'trash', 'per_page' => 100 ) )->get_data(), 'id' ), true ) );
	$restored = $request( 'PATCH', '/shortcodes/' . $dup_item['id'], array( 'status' => 'draft' ) );
	$check( 'restore from trash with PATCH status', 'draft' === $restored->get_data()['status'] );
	$gone = $request( 'DELETE', '/shortcodes/' . $dup_item['id'], array( 'force' => true ) );
	$check( 'DELETE force -> { deleted, previous }', 200 === $status( $gone ) && true === $gone->get_data()['deleted'] && $dup_item['id'] === $gone->get_data()['previous']['id'] && null === DB::get( $dup_item['id'] ) );
} finally {
	remove_all_filters( 'uts_builder_capability' );
	remove_filter( 'user_has_cap', $as_editor );
	wp_set_current_user( $admin );
	if ( isset( $uts_posts ) ) {
		foreach ( $uts_posts as $uts_post ) {
			if ( get_post( $uts_post ) ) {
				wp_delete_post( $uts_post, true );
			}
		}
	}
	foreach ( array_unique( array_filter( $uts_created ) ) as $created_id ) {
		if ( DB::get( $created_id ) ) {
			DB::delete( $created_id, true );
		}
	}
	global $wpdb;
	$table = DB::table();
	WP_CLI::line( '-- Cleanup: ' . (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ) . " row(s) left in {$table}" ); // phpcs:ignore WordPress.DB
}

if ( $uts_failed ) {
	WP_CLI::error( "{$uts_failed} check(s) failed." );
}
WP_CLI::success( 'All checks passed.' );
