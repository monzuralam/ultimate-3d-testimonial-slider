<?php
/**
 * Getting Started data: setup status and shortcode attribute docs.
 *
 * @package UTS
 * @since 1.1.0
 * @author Monzur Alam
 */

namespace UTS;

defined( 'ABSPATH' ) || exit;

/**
 * Data for the Getting Started page, which is rendered by the React admin
 * app (src/admin-app/pages/GettingStarted.js). Sent to it as boot data
 * (Menu::app_data()) and through GET /uts/v1/status.
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
class Getting_Started {

	/**
	 * No hooks of its own.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function register() {}

	/**
	 * Setup progress, from live site data.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return array
	 */
	public static function setup_status() {
		global $wpdb;

		$count     = wp_count_posts('uts');
		$published = isset($count->publish) ? (int) $count->publish : 0;

		$with_photo = 0;
		if ($published > 0) {
			$with_photo = count(get_posts(array(
				'post_type'      => 'uts',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_key'       => '_thumbnail_id', // phpcs:ignore WordPress.DB.SlowDBQuery
				'no_found_rows'  => true,
			)));
		}

		// Most recently modified published post/page containing the shortcode.
		// Cached until any post changes ('posts' last_changed is bumped by core on every save/delete).
		$cache_key = 'shortcode_page_' . wp_cache_get_last_changed('posts');
		$page_id   = wp_cache_get($cache_key, DB::CACHE_GROUP);
		if (false === $page_id) {
			// Direct query: WP_Query can only search post content alone from WP 6.2 (search_columns).
			$page_id = (int) $wpdb->get_var($wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				"SELECT ID FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type NOT IN ('revision', 'uts') AND post_content LIKE %s ORDER BY post_modified DESC LIMIT 1",
				'%' . $wpdb->esc_like('[uts') . '%'
			));
			wp_cache_set($cache_key, $page_id, DB::CACHE_GROUP);
		}
		$page_id = (int) $page_id;

		return array(
			'published'      => $published,
			'with_photo'     => $with_photo,
			'settings_saved' => false !== get_option('uts_settings', false),
			'page_id'        => $page_id,
		);
	}

	/**
	 * Descriptions for the shortcode reference table.
	 *
	 * Keys must match Settings::schema(), plus `category`. Add a description
	 * here when adding a setting.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return array
	 */
	public static function attribute_docs() {
		return array(
			'type'              => __('Layout: slider, grid, list, masonry or ticker.', 'ultimate-3d-testimonial-slider'),
			'count'             => __('How many testimonials to show. 0 shows all.', 'ultimate-3d-testimonial-slider'),
			'category'          => __('Only show testimonials from this category (use the category slug).', 'ultimate-3d-testimonial-slider'),
			'excerpt_length'    => __('Words of the testimonial to show. 0 shows the full text.', 'ultimate-3d-testimonial-slider'),
			'image_size'        => __('Image size: thumbnail, medium, large or full.', 'ultimate-3d-testimonial-slider'),
			'effect'            => __('Slider effect: coverflow, slide, fade, cards, cube, flip or creative.', 'ultimate-3d-testimonial-slider'),
			'speed'             => __('Transition speed in milliseconds.', 'ultimate-3d-testimonial-slider'),
			'autoplay'          => __('Move to the next slide automatically: true or false.', 'ultimate-3d-testimonial-slider'),
			'autoplay_delay'    => __('Time between slides when autoplay is on, in milliseconds.', 'ultimate-3d-testimonial-slider'),
			'loop'              => __('Go back to the first slide after the last one: true or false. Works with any number of testimonials.', 'ultimate-3d-testimonial-slider'),
			'pagination'        => __('Show dots under the slider: true or false.', 'ultimate-3d-testimonial-slider'),
			'navigation'        => __('Show previous/next arrows: true or false.', 'ultimate-3d-testimonial-slider'),
			'coverflow_rotate'  => __('Coverflow only: how far side slides turn, in degrees.', 'ultimate-3d-testimonial-slider'),
			'coverflow_depth'   => __('Coverflow only: how far side slides sit back.', 'ultimate-3d-testimonial-slider'),
			'coverflow_stretch' => __('Coverflow only: space between slides (negative overlaps them).', 'ultimate-3d-testimonial-slider'),
			'show_image'        => __('Show the client photo: true or false.', 'ultimate-3d-testimonial-slider'),
			'show_title'        => __('Show the client name: true or false.', 'ultimate-3d-testimonial-slider'),
			'show_designation'  => __('Show the position and company: true or false.', 'ultimate-3d-testimonial-slider'),
			'grid_columns'      => __('Grid and masonry only: columns on desktop, 1 to 6.', 'ultimate-3d-testimonial-slider'),
			'ticker_duration'   => __('Ticker only: seconds each testimonial takes to scroll past (higher is slower).', 'ultimate-3d-testimonial-slider'),
			'accent_color'      => __('Color of the dots and arrows, e.g. #2271b1.', 'ultimate-3d-testimonial-slider'),
			'text_color'        => __('Text color of the cards.', 'ultimate-3d-testimonial-slider'),
			'background_color'  => __('Background color of the cards.', 'ultimate-3d-testimonial-slider'),
			'border_radius'     => __('Card corner radius in pixels.', 'ultimate-3d-testimonial-slider'),
		);
	}

	/**
	 * YouTube ID of the tutorial video, or '' for the "coming soon" placeholder.
	 *
	 * Set the URL in uts_link( 'video' ) or with the `uts_getting_started_video` filter.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @return string
	 */
	public static function video_id() {
		return uts_youtube_id( apply_filters( 'uts_getting_started_video', uts_link( 'video' ) ) );
	}
}
