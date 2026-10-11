<?php
/**
 * The [uts] shortcode.
 *
 * @package UTS
 * @since 1.1.0
 * @author Monzur Alam
 */

namespace UTS;

defined( 'ABSPATH' ) || exit;

/**
 * Renders testimonials as a slider, grid or list via [uts].
 *
 * @since 1.1.0
 * @author Monzur Alam
 */
class Shortcode {

	/**
	 * Instances rendered so far in this request, per layout type.
	 *
	 * Static so every caller (the shortcode and the uts_shortcode()
	 * function) shares one counter.
	 *
	 * @var array
	 */
	private static $instances = array();

	/**
	 * Register the shortcode.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 */
	public function register() {
		add_shortcode( 'uts', array( $this, 'render' ) );
	}

	/**
	 * Render the shortcode.
	 *
	 * Runs a WP_Query on `uts` posts, optionally filtered by category, and
	 * outputs them as a slider, grid or list. Defaults come from the global
	 * settings; any setting key can be overridden as an attribute,
	 * e.g. [uts type="grid" grid_columns="4"].
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param array|string $atts Shortcode attributes. Main attributes:
	 *     - 'count' (int) : Number of testimonials to display. 0 = all.
	 *     - 'category' (string) : Slug of the category to filter testimonials.
	 *     - 'type' (string) : slider, grid or list.
	 * @return string The HTML output.
	 */
	public function render( $atts ) {
		// Ignore empty attributes (e.g. count="") so the setting applies.
		$atts = array_filter( (array) $atts, 'strlen' );

		$settings  = Settings::sanitize( shortcode_atts( Settings::get(), $atts, 'uts' ) );
		$category  = isset( $atts['category'] ) ? sanitize_text_field( $atts['category'] ) : '';
		$type      = $settings['type'];
		$is_slider = 'slider' === $type;

		$args = array(
			'post_type'      => 'uts',
			'post_status'    => 'publish',
			'posts_per_page' => $settings['count'] > 0 ? $settings['count'] : -1,
			'order'          => 'ASC',
			'orderby'        => 'menu_order',
		);

		// Category.
		if ( '' !== $category ) {
			$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'uts_category',
					'field'    => 'slug',
					'terms'    => $category,
				),
			);
		}

		// Unique ID per instance; the first keeps the original ID for custom CSS.
		self::$instances[ $type ] = isset( self::$instances[ $type ] ) ? self::$instances[ $type ] + 1 : 1;
		$container_id             = 'ucs-container-' . $type . ( self::$instances[ $type ] > 1 ? '-' . self::$instances[ $type ] : '' );

		$classes = array( 'ucs-container', $type );
		if ( $is_slider ) {
			$classes[] = 'swiper';
			$classes[] = 'uts-effect-' . $settings['effect'];
		}

		Assets::enqueue_frontend();

		$query = new \WP_Query( $args );
		ob_start();
		?>
	<section id="<?php echo esc_attr( $container_id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php
		$style = self::style( $settings );
		if ( '' !== $style ) {
			echo ' style="' . esc_attr( $style ) . '"';
		}
		if ( $is_slider ) {
			echo ' data-uts-config="' . esc_attr( wp_json_encode( self::slider_config( $settings ) ) ) . '"';
		}
		?>>
		<div class="<?php echo $is_slider ? 'swiper-wrapper' : 'ucs-wrapper'; ?>">
			<?php
			if ( $query->have_posts() ) :
				while ( $query->have_posts() ) :
					$query->the_post();
					?>
					<div class="ucs-item <?php echo $is_slider ? 'swiper-slide' : ''; ?>">
						<?php
						if ( $settings['show_image'] ) {
							if ( has_post_thumbnail() ) {
								the_post_thumbnail( $settings['image_size'] );
							} else {
								?>
								<img src="<?php echo esc_url( UTS_URL . '/assets/images/quotation.png' ); ?>" alt="<?php esc_attr_e( 'Quote', 'uts' ); ?>" class="img-fluid">
								<?php
							}
						}
						?>
						<div class="details-content">
							<p><?php
								$content = wp_strip_all_tags( strip_shortcodes( get_the_content() ) );
								echo esc_html( $settings['excerpt_length'] > 0 ? wp_trim_words( $content, $settings['excerpt_length'] ) : $content );
							?></p>
							<?php if ( $settings['show_title'] ) { ?>
								<h3><?php echo esc_html( get_the_title() ); ?></h3>
							<?php } ?>
							<?php
							$designation = get_post_meta( get_the_ID(), 'uts_designation', true );
							if ( $settings['show_designation'] && '' !== $designation ) {
								?>
								<h4><?php echo esc_html( $designation ); ?></h4>
							<?php } ?>
						</div>
					</div>
					<?php
				endwhile;
			endif;
			wp_reset_postdata();
			?>
		</div>
		<?php if ( $is_slider && $settings['pagination'] ) { ?>
			<div class="swiper-pagination"></div>
		<?php } ?>
		<?php if ( $is_slider && $settings['navigation'] ) { ?>
			<div class="swiper-button-prev"></div>
			<div class="swiper-button-next"></div>
		<?php } ?>
	</section>
		<?php
		return ob_get_clean();
	}

	/**
	 * Inline CSS variables for one shortcode instance.
	 *
	 * Only values that differ from the stylesheet defaults are output.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param array $settings Sanitized settings.
	 * @return string
	 */
	public static function style( $settings ) {
		$vars = array();

		if ( '' !== $settings['accent_color'] ) {
			$vars['--swiper-theme-color'] = $settings['accent_color'];
		}
		if ( '' !== $settings['text_color'] ) {
			$vars['--uts-text'] = $settings['text_color'];
		}
		if ( '' !== $settings['background_color'] ) {
			$vars['--uts-bg'] = $settings['background_color'];
		}
		if ( $settings['border_radius'] > 0 ) {
			$vars['--uts-radius'] = $settings['border_radius'] . 'px';
		}
		if ( 'grid' === $settings['type'] && 3 !== $settings['grid_columns'] ) {
			$vars['--uts-columns']    = $settings['grid_columns'];
			$vars['--uts-columns-md'] = min( 2, $settings['grid_columns'] );
		}

		$style = '';
		foreach ( $vars as $name => $value ) {
			$style .= $name . ':' . $value . ';';
		}

		return $style;
	}

	/**
	 * Swiper options for one slider instance, read by src/frontend/index.js.
	 *
	 * @since 1.1.0
	 * @author Monzur Alam
	 *
	 * @param array $settings Sanitized settings.
	 * @return array
	 */
	public static function slider_config( $settings ) {
		$config = array(
			'effect'     => $settings['effect'],
			'speed'      => $settings['speed'],
			'loop'       => $settings['loop'],
			'autoplay'   => $settings['autoplay'] ? $settings['autoplay_delay'] : false,
			'pagination' => $settings['pagination'],
			'navigation' => $settings['navigation'],
		);

		if ( 'coverflow' === $settings['effect'] ) {
			$config['coverflow'] = array(
				'rotate'  => $settings['coverflow_rotate'],
				'depth'   => $settings['coverflow_depth'],
				'stretch' => $settings['coverflow_stretch'],
			);
		}

		return apply_filters( 'uts_slider_config', $config, $settings );
	}
}
