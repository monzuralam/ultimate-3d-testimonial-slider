<?php
/**
 * Tests for star ratings and card styles.
 *
 * Run from the WordPress root:
 *
 *     wp eval-file wp-content/plugins/ultimate-3d-testimonial-slider/tests/rating.php
 *
 * Exits 0 when every check passes, 1 otherwise. Adds one temporary published
 * testimonial and deletes it again; other testimonials are not touched.
 *
 * @package UTS
 * @since 1.1.0
 * @author Monzur Alam
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

use UTS\Metabox;
use UTS\Shortcode;

$uts_failed = 0;

$check = function ( $label, $ok, $detail = '' ) use ( &$uts_failed ) {
	if ( ! $ok ) {
		$uts_failed++;
	}
	WP_CLI::line( ( $ok ? 'PASS ' : 'FAIL ' ) . $label . ( $ok || '' === $detail ? '' : ' -- ' . $detail ) );
};

// The rendered card of the temporary testimonial (found by its unique name).
$card = function ( $html, $name ) {
	foreach ( preg_split( '/(?=<div class="ucs-item)/', $html ) as $chunk ) {
		if ( false !== strpos( $chunk, '<h3>' . $name . '</h3>' ) ) {
			return $chunk;
		}
	}
	return '';
};

// Run Metabox::save() as a form submission.
$save = function ( $post_id, $post ) {
	$saved_post = $_POST; // phpcs:ignore WordPress.Security.NonceVerification
	$_POST      = $post;
	UTS\Plugin::instance()->component( 'UTS\Metabox' )->save( $post_id );
	$_POST = $saved_post;
	return Metabox::rating( $post_id );
};

$admin = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
$user  = get_current_user_id();
wp_set_current_user( (int) $admin[0] );

$name = 'UTS rating test ' . wp_generate_password( 6, false );
$id   = wp_insert_post(
	array(
		'post_type'    => 'uts',
		'post_status'  => 'publish',
		'post_title'   => $name,
		'post_content' => 'Rated testimonial.',
		'menu_order'   => -1000, // First in every list.
	)
);

try {
	// ------------------------------------------------------------------
	WP_CLI::line( '-- rating_html()' );
	$check( 'no rating -> empty', '' === Shortcode::rating_html( 0 ) && '' === Shortcode::rating_html( -3 ) );
	$four = Shortcode::rating_html( 4 );
	$check( '5 stars, 4 filled', 5 === substr_count( $four, '<svg' ) && 4 === substr_count( $four, 'is-on' ) );
	$check( 'accessible label', false !== strpos( $four, 'role="img" aria-label="Rated 4 out of 5"' ) );
	$check( 'clamped to 5', 5 === substr_count( Shortcode::rating_html( 9 ), 'is-on' ) );

	// ------------------------------------------------------------------
	WP_CLI::line( '-- Metabox::save()' );
	$nonce = wp_create_nonce( Metabox::NONCE_ACTION );
	$form  = function ( $rating ) use ( $nonce ) {
		return array(
			Metabox::NONCE_FIELD => $nonce,
			'uts_rating'         => $rating,
		);
	};
	$check( 'new testimonial has no rating', 0 === Metabox::rating( $id ) && '' === get_post_meta( $id, Metabox::RATING_KEY, true ) );
	$check( 'saves 4', 4 === $save( $id, $form( '4' ) ) );
	$check( 'no nonce -> unchanged', 4 === $save( $id, array( 'uts_rating' => '2' ) ) );
	$check( 'bad nonce -> unchanged', 4 === $save( $id, array( Metabox::NONCE_FIELD => 'nope', 'uts_rating' => '2' ) ) );
	wp_set_current_user( 0 );
	$check( 'no capability -> unchanged', 4 === $save( $id, array( Metabox::NONCE_FIELD => wp_create_nonce( Metabox::NONCE_ACTION ), 'uts_rating' => '2' ) ) );
	wp_set_current_user( (int) $admin[0] );
	$check( 'out of range (9) -> rating removed', 0 === $save( $id, $form( '9' ) ) && ! metadata_exists( 'post', $id, Metabox::RATING_KEY ) );
	$save( $id, $form( '3' ) );
	$check( '"No rating" (0) -> meta deleted', 0 === $save( $id, $form( '0' ) ) && ! metadata_exists( 'post', $id, Metabox::RATING_KEY ) );
	$check( 'junk -> meta deleted', 0 === $save( $id, $form( 'abc' ) ) );
	update_post_meta( $id, Metabox::RATING_KEY, 7 );
	$check( 'stored out-of-range value reads as no rating', 0 === Metabox::rating( $id ) );
	$save( $id, $form( '5' ) );

	ob_start();
	UTS\Plugin::instance()->component( 'UTS\Metabox' )->render( get_post( $id ) );
	$box = ob_get_clean();
	$check( 'metabox shows the rating select with 5 selected', false !== strpos( $box, 'name="uts_rating"' ) && (bool) preg_match( '/<option value="5"\s+selected/', $box ) );

	// ------------------------------------------------------------------
	WP_CLI::line( '-- Frontend' );
	$html  = do_shortcode( '[uts type="grid" count="100"]' );
	$rated = $card( $html, $name );
	$check( 'rated testimonial shows its stars', false !== strpos( $rated, 'aria-label="Rated 5 out of 5"' ), substr( $rated, 0, 200 ) );
	$check( 'stars come before the text', strpos( $rated, 'ucs-rating' ) < strpos( $rated, '<p>' ) );
	$rated_ids = array_filter( get_posts( array( 'post_type' => 'uts', 'posts_per_page' => -1, 'fields' => 'ids' ) ), array( 'UTS\Metabox', 'rating' ) );
	$check( 'only rated testimonials show stars', count( $rated_ids ) === substr_count( $html, 'class="ucs-rating"' ) );
	$check( 'show_rating="false" hides all stars', false === strpos( do_shortcode( '[uts type="grid" count="100" show_rating="false"]' ), 'ucs-rating' ) );
	delete_post_meta( $id, Metabox::RATING_KEY );
	$check( 'unrated -> no rating markup', false === strpos( $card( do_shortcode( '[uts type="grid" count="100"]' ), $name ), 'ucs-rating' ) );
	update_post_meta( $id, Metabox::RATING_KEY, 3 );

	$check( 'star_color -> --uts-star', false !== strpos( do_shortcode( '[uts star_color="#ff0000"]' ), '--uts-star:#ff0000' ) );
	$check( 'invalid star_color ignored', false === strpos( do_shortcode( '[uts star_color="red"]' ), '--uts-star' ) );

	// ------------------------------------------------------------------
	WP_CLI::line( '-- Card styles' );
	$check( 'classic adds no class', false === strpos( do_shortcode( '[uts]' ), 'ucs-card-' ) && false === strpos( do_shortcode( '[uts card_style="classic"]' ), 'ucs-card-' ) );
	foreach ( array( 'modern', 'bubble', 'elegant', 'glass', 'dark', 'split' ) as $style ) {
		foreach ( array( 'slider', 'grid', 'list', 'masonry', 'ticker' ) as $type ) {
			$out = do_shortcode( '[uts type="' . $type . '" card_style="' . $style . '"]' );
			if ( ! preg_match( '/<section [^>]*class="ucs-container ' . $type . '[^"]* ucs-card-' . $style . '"/', $out ) ) {
				$check( "{$style} on {$type}", false, substr( $out, 0, 160 ) );
				continue 2;
			}
		}
		$check( "{$style}: ucs-card-{$style} on every layout", true );
	}
	$check( 'unknown style falls back to classic', false === strpos( do_shortcode( '[uts card_style="neon"]' ), 'ucs-card-' ) );
	$check( 'schema default is classic', 'classic' === UTS\Settings::defaults()['card_style'] );

	// ------------------------------------------------------------------
	WP_CLI::line( '-- Admin column' );
	$columns = UTS\Plugin::instance()->component( 'UTS\Admin_Columns' );
	$check( 'Rating column after Position & Company', (bool) preg_match( '/uts_designation,uts_rating/', implode( ',', array_keys( $columns->columns( array( 'cb' => '', 'title' => '', 'date' => '' ) ) ) ) ) );
	ob_start();
	$columns->column_content( 'uts_rating', $id );
	$cell = ob_get_clean();
	$check( 'rated cell: 3 filled stars + screen reader text', false !== strpos( $cell, '★★★☆☆' ) && false !== strpos( $cell, 'Rated 3 out of 5' ), $cell );
	delete_post_meta( $id, Metabox::RATING_KEY );
	ob_start();
	$columns->column_content( 'uts_rating', $id );
	$check( 'unrated cell: dash', false !== strpos( ob_get_clean(), '&mdash;' ) );
} finally {
	wp_delete_post( $id, true );
	wp_set_current_user( $user );
	WP_CLI::line( '-- Cleanup: temporary testimonial ' . ( get_post( $id ) ? 'NOT deleted' : 'deleted' ) );
}

if ( $uts_failed ) {
	WP_CLI::error( "{$uts_failed} check(s) failed." );
}
WP_CLI::success( 'All checks passed.' );
