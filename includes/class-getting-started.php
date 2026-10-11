<?php
/**
 * Getting Started page.
 *
 * @package UTS
 * @since 1.1.0
 * @author Monzur Alam
 */

namespace UTS;

defined( 'ABSPATH' ) || exit;

/**
 * The Getting Started dashboard: welcome and tutorial video, live preview,
 * setup checklist, "How to use" guide, shortcode reference and resources.
 *
 * The page itself is registered by Menu; this class renders it and loads
 * its assets.
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
class Getting_Started {

	/**
	 * No hooks of its own: Menu registers the page and calls load()/render().
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function register() {}

	/**
	 * Page load: enqueue assets (the frontend build too, for the live preview).
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function load() {
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
	}

	/**
	 * Getting Started page assets.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function assets() {
		if (uts_register_build('uts', 'frontend')) {
			Assets::enqueue_frontend();
		}
	}

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
	 * Tutorial video in the welcome card.
	 *
	 * Shows a placeholder until a URL is set in uts_link('video') (or via the
	 * `uts_getting_started_video` filter). With a URL, the thumbnail is shown
	 * and the youtube-nocookie player is created on click (see src/admin/index.js).
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function video() {
		$video_id = uts_youtube_id(apply_filters('uts_getting_started_video', uts_link('video')));
		$title    = __('Watch: set up your testimonial slider in a few minutes', 'uts');
		?>
		<div class="uts:mt-6 uts:aspect-video uts:w-full uts:max-w-4xl uts:overflow-hidden uts:rounded-lg uts:bg-gray-900">
			<?php if ('' !== $video_id) { ?>
				<button type="button" class="uts-video uts:group uts:relative uts:block uts:size-full uts:cursor-pointer uts:border-0 uts:bg-transparent uts:p-0"
					data-uts-video="<?php echo esc_attr($video_id); ?>"
					data-uts-video-title="<?php echo esc_attr($title); ?>"
					aria-label="<?php echo esc_attr__('Play video:', 'uts') . ' ' . esc_attr($title); ?>">
					<img src="<?php echo esc_url('https://i.ytimg.com/vi/' . $video_id . '/hqdefault.jpg'); ?>" alt="" loading="lazy" class="uts:size-full uts:object-cover uts:opacity-90 uts:transition-opacity uts:group-hover:opacity-100">
					<span class="uts:absolute uts:inset-0 uts:flex uts:items-center uts:justify-center">
						<span class="uts:flex uts:size-18 uts:items-center uts:justify-center uts:rounded-full uts:bg-white/95 uts:shadow-lg uts:transition-transform uts:group-hover:scale-110">
							<span class="dashicons dashicons-controls-play uts:ml-1 uts:size-9 uts:text-4xl uts:text-wp-blue" aria-hidden="true"></span>
						</span>
					</span>
				</button>
			<?php } else { ?>
				<div class="uts:relative uts:flex uts:size-full uts:flex-col uts:items-center uts:justify-center uts:gap-4 uts:bg-linear-to-br uts:from-wp-blue uts:to-indigo-900 uts:p-6 uts:text-center">
					<span class="uts:flex uts:size-18 uts:items-center uts:justify-center uts:rounded-full uts:bg-white/15 uts:ring-1 uts:ring-white/30">
						<span class="dashicons dashicons-controls-play uts:ml-1 uts:size-9 uts:text-4xl uts:text-white" aria-hidden="true"></span>
					</span>
					<p class="uts:m-0 uts:text-xl uts:font-semibold uts:text-white"><?php esc_html_e('Video tutorial coming soon', 'uts'); ?></p>
					<p class="uts:m-0 uts:max-w-md uts:text-white/80"><?php esc_html_e('A short walkthrough of adding testimonials and placing the slider. Until then, the "How to use" guide below covers every step.', 'uts'); ?></p>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Page callback.
	 *
	 * @since 1.0.1
	 * @author Monzur Alam
	 */
	public function render() {
		$status = self::setup_status();
		$user   = wp_get_current_user();
		$can_settings = current_user_can('manage_options');

		$checklist = array(
			array(
				'done'  => $status['published'] > 0,
				'title' => __('Add your testimonials', 'uts'),
				'text'  => $status['published'] > 0
					/* translators: %s: number of testimonials */
					? sprintf(_n('%s testimonial published.', '%s testimonials published.', $status['published'], 'uts'), number_format_i18n($status['published']))
					: __('Add at least one testimonial to show on your site.', 'uts'),
				'url'   => admin_url('post-new.php?post_type=uts'),
				'link'  => __('Add Testimonial', 'uts'),
			),
			array(
				'done'  => $status['published'] > 0 && $status['with_photo'] === $status['published'],
				'title' => __('Add client photos', 'uts'),
				'text'  => $status['published'] > 0
					/* translators: 1: testimonials with a photo, 2: all testimonials */
					? sprintf(__('%1$s of %2$s testimonials have a photo. Without one, a quote icon is shown.', 'uts'), number_format_i18n($status['with_photo']), number_format_i18n($status['published']))
					: __('Set a featured image on each testimonial.', 'uts'),
				'url'   => $status['published'] > $status['with_photo'] ? admin_url('edit.php?post_type=uts&uts_photo=missing') : admin_url('edit.php?post_type=uts'),
				'link'  => $status['published'] > $status['with_photo']
					/* translators: %s: number of testimonials without a photo */
					? sprintf(__('Show the %s without a photo', 'uts'), number_format_i18n($status['published'] - $status['with_photo']))
					: __('View Testimonials', 'uts'),
			),
			array(
				'done'  => $status['settings_saved'],
				'title' => __('Choose your look', 'uts'),
				'text'  => $status['settings_saved']
					? __('Settings saved. You can change them at any time.', 'uts')
					: __('Pick the layout, slider effect, autoplay and colors. The defaults work too.', 'uts'),
				'url'   => $can_settings ? admin_url('edit.php?post_type=uts&page=uts-settings') : '',
				'link'  => __('Open Settings', 'uts'),
			),
			array(
				'done'  => $status['page_id'] > 0,
				'title' => __('Show them on a page', 'uts'),
				'text'  => $status['page_id'] > 0
					/* translators: %s: page title */
					? sprintf(__('Used on "%s".', 'uts'), get_the_title($status['page_id']))
					: __('Paste [uts] into any page. See "How to use" below.', 'uts'),
				'url'   => $status['page_id'] > 0 ? get_permalink($status['page_id']) : admin_url('post-new.php?post_type=page'),
				'link'  => $status['page_id'] > 0 ? __('View Page', 'uts') : __('Create a Page', 'uts'),
			),
		);
		$done = count(array_filter(wp_list_pluck($checklist, 'done')));
		?>
		<?php Menu::header('getting-started'); ?>
		<div class="wrap uts-getting-started uts:mt-6 uts:max-w-[1280px]">
			<h1 class="screen-reader-text"><?php esc_html_e('Getting Started', 'uts'); ?></h1>

			<div class="uts:grid uts:grid-cols-1 uts:gap-6 uts:xl:grid-cols-[minmax(0,1fr)_320px]">
				<div class="uts:flex uts:min-w-0 uts:flex-col uts:gap-6">

					<?php // Welcome ?>
					<?php uts_card_open(); ?>
						<p class="uts:m-0 uts:text-[28px] uts:leading-tight uts:font-semibold uts:text-gray-900">
							<?php
							/* translators: %s: user display name */
							printf(esc_html__('Welcome, %s', 'uts'), esc_html($user->display_name));
							?> 👋
						</p>
						<p class="uts:mt-3 uts:mb-0 uts:max-w-2xl uts:text-[15px] uts:text-wp-muted">
							<?php esc_html_e('Turn what your clients say into a 3D slider, grid or list. Add testimonials, place one shortcode, and you are done.', 'uts'); ?>
						</p>
						<div class="uts:mt-5 uts:flex uts:flex-wrap uts:gap-3">
							<a href="<?php echo esc_url(admin_url('post-new.php?post_type=uts')); ?>" class="button button-primary button-hero uts:inline-flex! uts:items-center uts:gap-1">
								<span class="dashicons dashicons-plus-alt2 uts:size-5 uts:text-xl uts:leading-none" aria-hidden="true"></span>
								<?php esc_html_e('Add Testimonial', 'uts'); ?>
							</a>
							<?php if ($can_settings) { ?>
								<a href="<?php echo esc_url(admin_url('edit.php?post_type=uts&page=uts-settings')); ?>" class="button button-hero uts:inline-flex! uts:items-center uts:gap-1">
									<span class="dashicons dashicons-admin-appearance uts:size-5 uts:text-xl uts:leading-none" aria-hidden="true"></span>
									<?php esc_html_e('Customize the Look', 'uts'); ?>
								</a>
							<?php } ?>
						</div>

						<?php $this->video(); ?>
					</section>

					<?php // Live preview ?>
					<?php uts_card_open('', 'uts:overflow-hidden'); ?>
						<div class="uts:mb-5 uts:flex uts:flex-wrap uts:items-center uts:justify-between uts:gap-3">
							<div>
								<h2 class="uts:m-0 uts:text-lg uts:font-semibold uts:text-gray-900"><?php esc_html_e('Live preview', 'uts'); ?></h2>
								<p class="uts:m-0 uts:mt-1 uts:text-wp-muted"><?php esc_html_e('Your testimonials with your current settings.', 'uts'); ?></p>
							</div>
							<?php if ($status['published'] > 0) { ?>
								<div class="uts:inline-flex uts:rounded-md uts:border uts:border-wp-border uts:p-0.5" role="tablist" aria-label="<?php esc_attr_e('Preview layout', 'uts'); ?>">
									<?php
									$layouts = array(
										'slider' => __('Slider', 'uts'),
										'grid'   => __('Grid', 'uts'),
										'list'   => __('List', 'uts'),
									);
									foreach ($layouts as $layout => $label) {
										$selected = 'slider' === $layout;
										?>
										<button type="button" role="tab" data-uts-preview="<?php echo esc_attr($layout); ?>" aria-selected="<?php echo $selected ? 'true' : 'false'; ?>"
											class="uts:cursor-pointer uts:rounded uts:border-0 uts:px-4 uts:py-1.5 uts:text-sm uts:font-medium uts:aria-selected:bg-wp-blue uts:aria-selected:text-white uts:bg-transparent uts:text-gray-700">
											<?php echo esc_html($label); ?>
										</button>
									<?php } ?>
								</div>
							<?php } ?>
						</div>

						<?php if ($status['published'] > 0) { ?>
							<div class="uts:rounded-md uts:bg-wp-bg uts:p-4 uts:md:p-8">
								<?php foreach (array_keys($layouts) as $layout) { ?>
									<div data-uts-preview-panel="<?php echo esc_attr($layout); ?>" role="tabpanel" <?php echo 'slider' === $layout ? '' : 'hidden'; ?>>
										<?php echo do_shortcode('[uts type="' . esc_attr($layout) . '" count="6"]'); // phpcs:ignore WordPress.Security.EscapeOutput -- shortcode output is escaped. ?>
									</div>
								<?php } ?>
							</div>
						<?php } else { ?>
							<div class="uts:flex uts:flex-col uts:items-center uts:rounded-md uts:border uts:border-dashed uts:border-wp-border uts:bg-wp-bg uts:px-6 uts:py-12 uts:text-center">
								<span class="dashicons dashicons-format-quote uts:size-12 uts:text-5xl uts:text-wp-border" aria-hidden="true"></span>
								<p class="uts:mt-3 uts:mb-4 uts:text-[15px] uts:text-wp-muted"><?php esc_html_e('No testimonials yet. Add your first one to see the preview here.', 'uts'); ?></p>
								<a href="<?php echo esc_url(admin_url('post-new.php?post_type=uts')); ?>" class="button button-primary"><?php esc_html_e('Add Testimonial', 'uts'); ?></a>
							</div>
						<?php } ?>
					</section>

					<?php
					// Setup checklist: full card while there is work left, one line once complete.
					$all_done = count($checklist) === $done;
					uts_card_open();
					?>
						<details <?php echo $all_done ? '' : 'open'; ?> class="uts-checklist">
							<summary class="uts:flex uts:flex-wrap uts:items-center uts:justify-between uts:gap-x-4 uts:gap-y-1 <?php echo $all_done ? 'uts:cursor-pointer' : 'uts:pointer-events-none uts:list-none'; ?>">
								<?php if ($all_done) { ?>
									<span class="uts:flex uts:items-center uts:gap-2 uts:text-[15px] uts:font-semibold uts:text-gray-900">
										<span class="dashicons dashicons-yes-alt uts:size-6 uts:text-2xl uts:text-green-600" aria-hidden="true"></span>
										<?php esc_html_e('All set! Your testimonials are live.', 'uts'); ?>
									</span>
									<span class="uts:text-sm uts:font-medium uts:text-wp-blue">
										<span class="uts-checklist__show"><?php esc_html_e('Show checklist', 'uts'); ?></span>
										<span class="uts-checklist__hide"><?php esc_html_e('Hide checklist', 'uts'); ?></span>
									</span>
								<?php } else { ?>
									<h2 class="uts:m-0 uts:text-lg uts:font-semibold uts:text-gray-900"><?php esc_html_e('Setup checklist', 'uts'); ?></h2>
									<span class="uts:text-sm uts:font-medium uts:text-wp-muted">
										<?php
										/* translators: 1: done steps, 2: all steps */
										printf(esc_html__('%1$s of %2$s done', 'uts'), (int) $done, count($checklist));
										?>
									</span>
								<?php } ?>
							</summary>
							<?php if (!$all_done) { ?>
								<div class="uts:mt-4 uts:mb-2 uts:h-2 uts:overflow-hidden uts:rounded-full uts:bg-wp-bg" role="progressbar" aria-valuemin="0" aria-valuemax="<?php echo count($checklist); ?>" aria-valuenow="<?php echo (int) $done; ?>" aria-label="<?php esc_attr_e('Setup progress', 'uts'); ?>">
									<div class="uts:h-full uts:rounded-full uts:bg-green-600" style="width: <?php echo esc_attr(round($done / count($checklist) * 100)); ?>%"></div>
								</div>
							<?php } ?>
							<ol class="uts:m-0 uts:mt-2 uts:flex uts:list-none uts:flex-col uts:divide-y uts:divide-wp-bg uts:p-0">
								<?php foreach ($checklist as $item) { ?>
									<li class="uts:m-0 uts:flex uts:flex-wrap uts:items-center uts:gap-x-4 uts:gap-y-2 uts:py-3">
										<span class="dashicons <?php echo $item['done'] ? 'dashicons-yes-alt uts:text-green-600' : 'dashicons-marker uts:text-wp-border'; ?> uts:size-6 uts:text-2xl" aria-hidden="true"></span>
										<div class="uts:min-w-48 uts:flex-1">
											<p class="uts:m-0 uts:flex uts:flex-wrap uts:items-center uts:gap-2 uts:font-semibold uts:text-gray-900">
												<?php echo esc_html($item['title']); ?>
												<?php if ($item['done']) { ?>
													<span class="uts:rounded-full uts:bg-green-50 uts:px-2 uts:py-0.5 uts:text-xs uts:font-medium uts:text-green-700"><?php esc_html_e('Done', 'uts'); ?></span>
												<?php } else { ?>
													<span class="screen-reader-text"><?php esc_html_e('(to do)', 'uts'); ?></span>
												<?php } ?>
											</p>
											<p class="uts:m-0 uts:text-wp-muted"><?php echo esc_html($item['text']); ?></p>
										</div>
										<?php if ('' !== $item['url']) { ?>
											<a class="button <?php echo $item['done'] ? '' : 'button-primary'; ?>" href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['link']); ?></a>
										<?php } ?>
									</li>
								<?php } ?>
							</ol>
						</details>
					</section>

					<?php // How to use ?>
					<?php uts_card_open(__('How to use', 'uts')); ?>
						<div class="uts-guide uts:flex uts:flex-col uts:gap-3">

							<details class="uts:group uts:rounded-md uts:border uts:border-wp-border" open>
								<summary class="uts:flex uts:cursor-pointer uts:items-center uts:gap-3 uts:p-4 uts:text-[15px] uts:font-semibold uts:text-gray-900">
									<span class="uts:flex uts:size-7 uts:shrink-0 uts:items-center uts:justify-center uts:rounded-full uts:bg-wp-blue uts:text-sm uts:text-white">1</span>
									<?php esc_html_e('Add a testimonial', 'uts'); ?>
								</summary>
								<div class="uts:border-t uts:border-wp-border uts:px-4 uts:pb-4 uts:md:pl-4">
									<ol class="uts:ml-4 uts:list-decimal uts:space-y-2 uts:text-[14px]">
										<li><?php
											/* translators: %s: menu path */
											printf(esc_html__('Go to %s.', 'uts'), '<strong>' . esc_html__('Ultimate 3D Testimonial Slider → Add Post', 'uts') . '</strong>');
										?></li>
										<li><?php echo wp_kses(__('<strong>Title:</strong> the client\'s name, e.g. "Jane Cooper".', 'uts'), array('strong' => array())); ?></li>
										<li><?php echo wp_kses(__('<strong>Content:</strong> what the client said. Keep it short; the excerpt length in Settings trims long ones.', 'uts'), array('strong' => array())); ?></li>
										<li><?php echo wp_kses(__('<strong>Client Featured Image:</strong> the client\'s photo. A square image of at least 160 × 160 px looks best, since it is shown as a circle.', 'uts'), array('strong' => array())); ?></li>
										<li><?php echo wp_kses(__('<strong>Position &amp; Company Name</strong> (below the editor): e.g. "CEO, Acme Inc."', 'uts'), array('strong' => array())); ?></li>
										<li><?php echo wp_kses(__('<strong>Order</strong> (Page Attributes box): lower numbers show first. Leave it at 0 to keep the default order.', 'uts'), array('strong' => array())); ?></li>
										<li><?php echo wp_kses(__('Click <strong>Publish</strong>. Only published testimonials are shown.', 'uts'), array('strong' => array())); ?></li>
									</ol>
								</div>
							</details>

							<details class="uts:rounded-md uts:border uts:border-wp-border">
								<summary class="uts:flex uts:cursor-pointer uts:items-center uts:gap-3 uts:p-4 uts:text-[15px] uts:font-semibold uts:text-gray-900">
									<span class="uts:flex uts:size-7 uts:shrink-0 uts:items-center uts:justify-center uts:rounded-full uts:bg-wp-blue uts:text-sm uts:text-white">2</span>
									<?php esc_html_e('Organize with categories (optional)', 'uts'); ?>
								</summary>
								<div class="uts:border-t uts:border-wp-border uts:px-4 uts:pb-4 uts:md:pl-4 uts:text-[14px]">
									<p><?php esc_html_e('Categories let you show different testimonials in different places, for example "Web Design" clients on one page and "SEO" clients on another.', 'uts'); ?></p>
									<ol class="uts:ml-4 uts:list-decimal uts:space-y-2">
										<li><?php echo wp_kses(__('Create categories under <strong>Category</strong> in the plugin menu, or tick them in the Category box while editing a testimonial.', 'uts'), array('strong' => array())); ?></li>
										<li><?php echo wp_kses(__('Note the category <strong>slug</strong> (the URL-friendly name, e.g. <code>web-design</code>) on the Category screen.', 'uts'), array('strong' => array(), 'code' => array())); ?></li>
										<li><?php esc_html_e('Use it in the shortcode:', 'uts'); ?></li>
									</ol>
									<div class="uts:mt-2 uts:max-w-md"><?php uts_copy_field('[uts category="web-design"]'); ?></div>
								</div>
							</details>

							<details class="uts:rounded-md uts:border uts:border-wp-border">
								<summary class="uts:flex uts:cursor-pointer uts:items-center uts:gap-3 uts:p-4 uts:text-[15px] uts:font-semibold uts:text-gray-900">
									<span class="uts:flex uts:size-7 uts:shrink-0 uts:items-center uts:justify-center uts:rounded-full uts:bg-wp-blue uts:text-sm uts:text-white">3</span>
									<?php esc_html_e('Show testimonials on your site', 'uts'); ?>
								</summary>
								<div class="uts:border-t uts:border-wp-border uts:px-4 uts:pb-4 uts:md:pl-4 uts:text-[14px]">
									<p><?php esc_html_e('Everything works with one shortcode. Copy it, then paste it where you want the testimonials:', 'uts'); ?></p>
									<div class="uts:mb-4 uts:max-w-md"><?php uts_copy_field('[uts]'); ?></div>
									<div class="uts:grid uts:grid-cols-1 uts:gap-3 uts:md:grid-cols-2">
										<?php
										$places = array(
											array('dashicons-block-default', __('Block editor (Gutenberg)', 'uts'), __('Click +, search for "Shortcode", add the Shortcode block and paste [uts].', 'uts')),
											array('dashicons-edit-page', __('Classic editor', 'uts'), __('Paste [uts] on its own line in the content.', 'uts')),
											array('dashicons-layout', __('Elementor and other page builders', 'uts'), __('Drag in the "Shortcode" widget/element and paste [uts].', 'uts')),
											array('dashicons-welcome-widgets-menus', __('Sidebar or footer widget', 'uts'), __('Appearance → Widgets (or Editor for block themes): add a Shortcode block and paste [uts].', 'uts')),
										);
										foreach ($places as $place) {
											?>
											<div class="uts:rounded-md uts:bg-wp-bg uts:p-4">
												<p class="uts:m-0 uts:flex uts:items-center uts:gap-2 uts:font-semibold uts:text-gray-900">
													<span class="dashicons <?php echo esc_attr($place[0]); ?> uts:text-wp-blue" aria-hidden="true"></span>
													<?php echo esc_html($place[1]); ?>
												</p>
												<p class="uts:m-0 uts:mt-1 uts:text-wp-muted"><?php echo esc_html($place[2]); ?></p>
											</div>
										<?php } ?>
									</div>
									<p class="uts:mt-4 uts:mb-1"><?php esc_html_e('In a theme template file (for developers):', 'uts'); ?></p>
									<pre class="uts:m-0 uts:overflow-x-auto uts:rounded-md uts:bg-gray-900 uts:p-3 uts:text-[13px] uts:text-gray-100"><code>&lt;?php echo do_shortcode( '[uts]' ); ?&gt;</code></pre>
								</div>
							</details>

							<details class="uts:rounded-md uts:border uts:border-wp-border">
								<summary class="uts:flex uts:cursor-pointer uts:items-center uts:gap-3 uts:p-4 uts:text-[15px] uts:font-semibold uts:text-gray-900">
									<span class="uts:flex uts:size-7 uts:shrink-0 uts:items-center uts:justify-center uts:rounded-full uts:bg-wp-blue uts:text-sm uts:text-white">4</span>
									<?php esc_html_e('Customize the design', 'uts'); ?>
								</summary>
								<div class="uts:border-t uts:border-wp-border uts:px-4 uts:pb-4 uts:md:pl-4 uts:text-[14px]">
									<p><?php esc_html_e('There are two levels, and you can mix them:', 'uts'); ?></p>
									<ul class="uts:ml-4 uts:list-disc uts:space-y-2">
										<li><?php echo wp_kses(__('<strong>Settings page</strong>: the defaults for every [uts] on your site, such as layout, effect, autoplay and colors.', 'uts'), array('strong' => array())); ?></li>
										<li><?php echo wp_kses(__('<strong>Shortcode attributes</strong>: change one slider only. Any setting can be used as an attribute with the same name, and it wins over the Settings page.', 'uts'), array('strong' => array())); ?></li>
									</ul>
									<p><?php esc_html_e('Examples:', 'uts'); ?></p>
									<div class="uts:grid uts:grid-cols-1 uts:gap-x-6 uts:gap-y-3 uts:lg:grid-cols-2">
										<?php
										$examples = array(
											'[uts type="grid" grid_columns="4"]'         => __('A 4-column grid.', 'uts'),
											'[uts type="list" count="3"]'                => __('A list of the first 3.', 'uts'),
											'[uts effect="fade" autoplay="true"]'        => __('Fading slider that plays by itself.', 'uts'),
											'[uts navigation="true" pagination="false"]' => __('Arrows instead of dots.', 'uts'),
											'[uts effect="cards" loop="true"]'           => __('Stacked cards that loop.', 'uts'),
											'[uts show_image="false" excerpt_length="0"]' => __('Full text, no photos.', 'uts'),
										);
										foreach ($examples as $shortcode => $description) {
											?>
											<div>
												<?php uts_copy_field($shortcode); ?>
												<p class="uts:m-0 uts:mt-1 uts:text-wp-muted"><?php echo esc_html($description); ?></p>
											</div>
										<?php } ?>
									</div>
								</div>
							</details>

							<details class="uts:rounded-md uts:border uts:border-wp-border">
								<summary class="uts:flex uts:cursor-pointer uts:items-center uts:gap-3 uts:p-4 uts:text-[15px] uts:font-semibold uts:text-gray-900">
									<span class="uts:flex uts:size-7 uts:shrink-0 uts:items-center uts:justify-center uts:rounded-full uts:bg-wp-blue uts:text-sm uts:text-white">5</span>
									<?php esc_html_e('Shortcode reference', 'uts'); ?>
								</summary>
								<div class="uts:overflow-x-auto uts:border-t uts:border-wp-border uts:p-4">
									<table class="widefat striped uts:min-w-[560px]">
										<thead>
											<tr>
												<th scope="col"><?php esc_html_e('Attribute', 'uts'); ?></th>
												<th scope="col"><?php esc_html_e('What it does', 'uts'); ?></th>
												<th scope="col"><?php esc_html_e('Default', 'uts'); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											$schema   = Settings::schema();
											$settings = Settings::get();
											foreach (self::attribute_docs() as $key => $description) {
												if ('category' !== $key && !isset($schema[$key])) {
													continue;
												}
												$default = 'category' === $key ? '' : $settings[$key];
												?>
												<tr>
													<td><code><?php echo esc_html($key); ?></code></td>
													<td><?php echo esc_html($description); ?></td>
													<td><code><?php echo esc_html(uts_format_default($default)); ?></code></td>
												</tr>
											<?php } ?>
										</tbody>
									</table>
									<p class="uts:mb-0 uts:text-wp-muted"><?php esc_html_e('"Default" shows your current Settings value, used when the attribute is left out.', 'uts'); ?></p>
								</div>
							</details>

							<details class="uts:rounded-md uts:border uts:border-wp-border">
								<summary class="uts:flex uts:cursor-pointer uts:items-center uts:gap-3 uts:p-4 uts:text-[15px] uts:font-semibold uts:text-gray-900">
									<span class="uts:flex uts:size-7 uts:shrink-0 uts:items-center uts:justify-center uts:rounded-full uts:bg-wp-blue uts:text-sm uts:text-white">?</span>
									<?php esc_html_e('Tips & troubleshooting', 'uts'); ?>
								</summary>
								<div class="uts:border-t uts:border-wp-border uts:px-4 uts:pb-4 uts:md:pl-14 uts:text-[14px]">
									<dl class="uts:m-0 uts:space-y-3">
										<div>
											<dt class="uts:font-semibold uts:text-gray-900"><?php esc_html_e('Nothing shows up on the page', 'uts'); ?></dt>
											<dd class="uts:m-0 uts:text-wp-muted"><?php esc_html_e('Check that the testimonials are published, that the category slug is spelled exactly right, and that the shortcode is not inside a code block. Clear your caching plugin after changes.', 'uts'); ?></dd>
										</div>
										<div>
											<dt class="uts:font-semibold uts:text-gray-900"><?php esc_html_e('More than one slider on a page', 'uts'); ?></dt>
											<dd class="uts:m-0 uts:text-wp-muted"><?php esc_html_e('Supported. Each [uts] works on its own with its own attributes.', 'uts'); ?></dd>
										</div>
										<div>
											<dt class="uts:font-semibold uts:text-gray-900"><?php esc_html_e('Changing the order', 'uts'); ?></dt>
											<dd class="uts:m-0 uts:text-wp-muted"><?php esc_html_e('Set "Order" in the Page Attributes box of each testimonial. Lower numbers come first.', 'uts'); ?></dd>
										</div>
										<div>
											<dt class="uts:font-semibold uts:text-gray-900"><?php esc_html_e('Autoplay or loop looks odd with few testimonials', 'uts'); ?></dt>
											<dd class="uts:m-0 uts:text-wp-muted"><?php esc_html_e('Loop needs at least 3 testimonials to look smooth; add more or turn loop off.', 'uts'); ?></dd>
										</div>
										<div>
											<dt class="uts:font-semibold uts:text-gray-900"><?php esc_html_e('Matching my theme', 'uts'); ?></dt>
											<dd class="uts:m-0 uts:text-wp-muted"><?php esc_html_e('Use the Style tab in Settings for colors and corner radius. For more, target .ucs-container and .ucs-item in your theme\'s custom CSS.', 'uts'); ?></dd>
										</div>
									</dl>
								</div>
							</details>

						</div>
					</section>
				</div>

				<?php // Sidebar ?>
				<aside class="uts:flex uts:flex-col uts:gap-6 uts:xl:sticky uts:xl:top-12 uts:xl:self-start">
					<?php
					$resources = array(
						array('dashicons-book', __('Documentation', 'uts'), __('Features, FAQ and the full shortcode reference on WordPress.org.', 'uts'), uts_link('docs'), __('Read the Docs', 'uts')),
						array('dashicons-sos', __('Need help?', 'uts'), __('Ask a question in the free support forum. We usually reply within a few days.', 'uts'), uts_link('support'), __('Open Support Forum', 'uts')),
						array('dashicons-star-filled', __('Enjoying the plugin?', 'uts'), __('A quick review on WordPress.org helps other people find it and keeps it improving.', 'uts'), uts_link('review'), __('Leave a Review', 'uts')),
						array('dashicons-editor-code', __('Source code', 'uts'), __('Report bugs, suggest features or contribute on GitHub.', 'uts'), uts_link('github'), __('View on GitHub', 'uts')),
					);
					foreach ($resources as $resource) {
						uts_card_open();
						?>
							<p class="uts:m-0 uts:flex uts:items-center uts:gap-2 uts:text-[15px] uts:font-semibold uts:text-gray-900">
								<span class="dashicons <?php echo esc_attr($resource[0]); ?> uts:text-wp-blue" aria-hidden="true"></span>
								<?php echo esc_html($resource[1]); ?>
							</p>
							<p class="uts:mt-2 uts:mb-4 uts:text-wp-muted"><?php echo esc_html($resource[2]); ?></p>
							<a class="button" href="<?php echo esc_url($resource[3]); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo esc_html($resource[4]); ?>
								<span class="screen-reader-text"><?php esc_html_e('(opens in a new tab)', 'uts'); ?></span>
							</a>
						</section>
					<?php } ?>
				</aside>
			</div>
		</div>
		<?php
	}
}
