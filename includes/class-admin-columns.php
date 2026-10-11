<?php
/**
 * Testimonials list table: extra columns and the "Without photo" view.
 *
 * @package UTS
 * @since 1.1.0
 * @author Monzur Alam
 */

namespace UTS;

defined( 'ABSPATH' ) || exit;

/**
 * Adds Photo, Position & Company and (sortable) Order columns to the
 * testimonials list, and a "Without photo" view with its query filter.
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
class Admin_Columns {

	/**
	 * Hook the columns, views and query filter.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function register() {
		add_filter( 'manage_uts_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_uts_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
		add_filter( 'manage_edit-uts_sortable_columns', array( $this, 'sortable_columns' ) );
		add_filter( 'views_edit-uts', array( $this, 'views' ) );
		add_action( 'pre_get_posts', array( $this, 'filter_without_photo' ) );
	}

	/**
	 * Add Photo (before Title), Position & Company and Order (before Date).
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param array $columns List table columns.
	 * @return array
	 */
	public function columns( $columns ) {
		$new = array();

		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$new['uts_photo'] = __( 'Photo', 'uts' );
			}
			$new[ $key ] = $label;
		}

		$date = isset( $new['date'] ) ? $new['date'] : null;
		unset( $new['date'] );
		$new['uts_designation'] = __( 'Position & Company', 'uts' );
		$new['uts_order']       = __( 'Order', 'uts' );
		if ( null !== $date ) {
			$new['date'] = $date;
		}

		return $new;
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
	public function column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'uts_photo':
				if ( has_post_thumbnail( $post_id ) ) {
					echo get_the_post_thumbnail( $post_id, array( 40, 40 ), array( 'class' => 'uts-list-photo', 'alt' => '' ) );
				} else {
					echo '<span class="uts-list-photo uts-list-photo--missing" title="' . esc_attr__( 'No photo', 'uts' ) . '"><span class="dashicons dashicons-format-quote" aria-hidden="true"></span><span class="screen-reader-text">' . esc_html__( 'No photo', 'uts' ) . '</span></span>';
				}
				break;

			case 'uts_designation':
				$designation = get_post_meta( $post_id, 'uts_designation', true );
				echo '' !== $designation ? esc_html( $designation ) : '<span aria-hidden="true">—</span>';
				break;

			case 'uts_order':
				echo (int) get_post_field( 'menu_order', $post_id );
				break;
		}
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
	public function sortable_columns( $columns ) {
		$columns['uts_order'] = 'menu_order';
		return $columns;
	}

	/**
	 * Number of published testimonials without a featured image.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return int
	 */
	public function count_without_photo() {
		$query = new \WP_Query(
			array(
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
			)
		);

		return (int) $query->found_posts;
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
	public function views( $views ) {
		$count = $this->count_without_photo();

		if ( $count < 1 ) {
			return $views;
		}

		$current = $this->is_without_photo_request();

		// Only this view should look selected while it is active.
		if ( $current ) {
			foreach ( $views as $key => $view ) {
				$views[ $key ] = str_replace( array( ' class="current"', ' aria-current="page"' ), '', $view );
			}
		}

		$views['uts_photo_missing'] = sprintf(
			'<a href="%s"%s>%s <span class="count">(%s)</span></a>',
			esc_url( admin_url( 'edit.php?post_type=uts&post_status=publish&uts_photo=missing' ) ),
			$current ? ' class="current" aria-current="page"' : '',
			esc_html__( 'Without photo', 'uts' ),
			esc_html( number_format_i18n( $count ) )
		);

		return $views;
	}

	/**
	 * Filter the testimonials list to those without a photo.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param \WP_Query $query Current query.
	 */
	public function filter_without_photo( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || 'uts' !== $query->get( 'post_type' ) ) {
			return;
		}

		if ( ! $this->is_without_photo_request() ) {
			return;
		}

		$query->set(
			'meta_query',
			array(
				array(
					'key'     => '_thumbnail_id',
					'compare' => 'NOT EXISTS',
				),
			)
		);
	}

	/**
	 * Whether the list was requested with ?uts_photo=missing.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return bool
	 */
	private function is_without_photo_request() {
		return isset( $_GET['uts_photo'] ) && 'missing' === $_GET['uts_photo']; // phpcs:ignore WordPress.Security.NonceVerification
	}
}
