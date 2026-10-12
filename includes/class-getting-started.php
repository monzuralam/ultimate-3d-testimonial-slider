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

		// First published post/page containing the shortcode.
		$page_id = (int) $wpdb->get_var($wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type NOT IN ('revision', 'uts') AND post_content LIKE %s ORDER BY post_modified DESC LIMIT 1",
			'%' . $wpdb->esc_like('[uts') . '%'
		));

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
			'type'              => __('Layout: slider, grid or list.', 'uts'),
			'count'             => __('How many testimonials to show. 0 shows all.', 'uts'),
			'category'          => __('Only show testimonials from this category (use the category slug).', 'uts'),
			'excerpt_length'    => __('Words of the testimonial to show. 0 shows the full text.', 'uts'),
			'image_size'        => __('Image size: thumbnail, medium, large or full.', 'uts'),
			'effect'            => __('Slider effect: coverflow, slide, fade or cards.', 'uts'),
			'speed'             => __('Transition speed in milliseconds.', 'uts'),
			'autoplay'          => __('Move to the next slide automatically: true or false.', 'uts'),
			'autoplay_delay'    => __('Time between slides when autoplay is on, in milliseconds.', 'uts'),
			'loop'              => __('Go back to the first slide after the last one: true or false.', 'uts'),
			'pagination'        => __('Show dots under the slider: true or false.', 'uts'),
			'navigation'        => __('Show previous/next arrows: true or false.', 'uts'),
			'coverflow_rotate'  => __('Coverflow only: how far side slides turn, in degrees.', 'uts'),
			'coverflow_depth'   => __('Coverflow only: how far side slides sit back.', 'uts'),
			'coverflow_stretch' => __('Coverflow only: space between slides (negative overlaps them).', 'uts'),
			'show_image'        => __('Show the client photo: true or false.', 'uts'),
			'show_title'        => __('Show the client name: true or false.', 'uts'),
			'show_designation'  => __('Show the position and company: true or false.', 'uts'),
			'grid_columns'      => __('Grid only: columns on desktop, 1 to 6.', 'uts'),
			'accent_color'      => __('Color of the dots and arrows, e.g. #2271b1.', 'uts'),
			'text_color'        => __('Text color of the cards.', 'uts'),
			'background_color'  => __('Background color of the cards.', 'uts'),
			'border_radius'     => __('Card corner radius in pixels.', 'uts'),
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
