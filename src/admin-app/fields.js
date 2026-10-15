/**
 * Settings page layout: tabs and the fields shown in each.
 *
 * Ranges, options and defaults come from the PHP schema
 * (uts_settings_schema()); this file only adds labels and UI.
 * `layouts` limits a field to those layouts in the Shortcode Builder.
 */
import { __ } from '@wordpress/i18n';

const isCoverflow = ( settings ) => 'coverflow' === settings.effect;

export const TABS = [
	{
		name: 'general',
		title: __( 'General', 'ultimate-3d-testimonial-slider' ),
		fields: [
			{
				key: 'type',
				control: 'select',
				label: __( 'Default layout', 'ultimate-3d-testimonial-slider' ),
				help: __(
					'Used when the shortcode has no type attribute.',
					'ultimate-3d-testimonial-slider'
				),
				options: {
					slider: __( '3D slider', 'ultimate-3d-testimonial-slider' ),
					grid: __( 'Grid', 'ultimate-3d-testimonial-slider' ),
					list: __( 'List', 'ultimate-3d-testimonial-slider' ),
					masonry: __( 'Masonry', 'ultimate-3d-testimonial-slider' ),
					ticker: __(
						'Ticker (scrolling row)',
						'ultimate-3d-testimonial-slider'
					),
				},
			},
			{
				key: 'count',
				control: 'range',
				label: __(
					'Testimonials to show',
					'ultimate-3d-testimonial-slider'
				),
				help: __(
					'0 shows all testimonials.',
					'ultimate-3d-testimonial-slider'
				),
			},
			{
				key: 'excerpt_length',
				control: 'range',
				label: __(
					'Excerpt length (words)',
					'ultimate-3d-testimonial-slider'
				),
				help: __(
					'0 shows the full testimonial.',
					'ultimate-3d-testimonial-slider'
				),
			},
			{
				key: 'image_size',
				control: 'select',
				label: __( 'Image size', 'ultimate-3d-testimonial-slider' ),
				options: {
					thumbnail: __(
						'Thumbnail',
						'ultimate-3d-testimonial-slider'
					),
					medium: __( 'Medium', 'ultimate-3d-testimonial-slider' ),
					large: __( 'Large', 'ultimate-3d-testimonial-slider' ),
					full: __( 'Full', 'ultimate-3d-testimonial-slider' ),
				},
			},
		],
	},
	{
		name: 'slider',
		title: __( 'Slider', 'ultimate-3d-testimonial-slider' ),
		fields: [
			{
				key: 'effect',
				control: 'select',
				label: __( 'Effect', 'ultimate-3d-testimonial-slider' ),
				options: {
					coverflow: __(
						'3D coverflow',
						'ultimate-3d-testimonial-slider'
					),
					slide: __( 'Slide', 'ultimate-3d-testimonial-slider' ),
					fade: __( 'Fade', 'ultimate-3d-testimonial-slider' ),
					cards: __( 'Cards', 'ultimate-3d-testimonial-slider' ),
					cube: __( '3D cube', 'ultimate-3d-testimonial-slider' ),
					flip: __( 'Flip', 'ultimate-3d-testimonial-slider' ),
					creative: __(
						'Creative (slide & zoom)',
						'ultimate-3d-testimonial-slider'
					),
				},
			},
			{
				key: 'speed',
				control: 'range',
				label: __(
					'Transition speed (ms)',
					'ultimate-3d-testimonial-slider'
				),
				step: 100,
			},
			{
				key: 'autoplay',
				control: 'toggle',
				label: __( 'Autoplay', 'ultimate-3d-testimonial-slider' ),
			},
			{
				key: 'autoplay_delay',
				control: 'range',
				label: __(
					'Autoplay delay (ms)',
					'ultimate-3d-testimonial-slider'
				),
				step: 500,
				showIf: ( settings ) => settings.autoplay,
			},
			{
				key: 'loop',
				control: 'toggle',
				label: __( 'Loop', 'ultimate-3d-testimonial-slider' ),
			},
			{
				key: 'pagination',
				control: 'toggle',
				label: __(
					'Pagination dots',
					'ultimate-3d-testimonial-slider'
				),
			},
			{
				key: 'navigation',
				control: 'toggle',
				label: __(
					'Navigation arrows',
					'ultimate-3d-testimonial-slider'
				),
			},
			{
				key: 'coverflow_rotate',
				control: 'range',
				label: __(
					'Coverflow rotate (deg)',
					'ultimate-3d-testimonial-slider'
				),
				showIf: isCoverflow,
			},
			{
				key: 'coverflow_depth',
				control: 'range',
				label: __(
					'Coverflow depth',
					'ultimate-3d-testimonial-slider'
				),
				showIf: isCoverflow,
			},
			{
				key: 'coverflow_stretch',
				control: 'range',
				label: __(
					'Coverflow stretch',
					'ultimate-3d-testimonial-slider'
				),
				showIf: isCoverflow,
			},
		],
	},
	{
		name: 'display',
		title: __( 'Display', 'ultimate-3d-testimonial-slider' ),
		fields: [
			{
				key: 'show_image',
				control: 'toggle',
				label: __( 'Show image', 'ultimate-3d-testimonial-slider' ),
			},
			{
				key: 'show_title',
				control: 'toggle',
				label: __(
					'Show client name',
					'ultimate-3d-testimonial-slider'
				),
			},
			{
				key: 'show_designation',
				control: 'toggle',
				label: __(
					'Show designation',
					'ultimate-3d-testimonial-slider'
				),
			},
			{
				key: 'show_rating',
				control: 'toggle',
				label: __(
					'Show star rating',
					'ultimate-3d-testimonial-slider'
				),
				help: __(
					'Only testimonials with a rating (set in "Client Additional" when editing a testimonial) show stars.',
					'ultimate-3d-testimonial-slider'
				),
			},
			{
				key: 'grid_columns',
				control: 'range',
				label: __( 'Columns', 'ultimate-3d-testimonial-slider' ),
				help: __(
					'Desktop columns for the grid and masonry layouts. Smaller screens use fewer.',
					'ultimate-3d-testimonial-slider'
				),
				layouts: [ 'grid', 'masonry' ],
			},
			{
				key: 'ticker_duration',
				control: 'range',
				label: __(
					'Ticker speed (seconds per testimonial)',
					'ultimate-3d-testimonial-slider'
				),
				help: __(
					'Higher is slower. The ticker pauses on hover and stays still for visitors who prefer reduced motion.',
					'ultimate-3d-testimonial-slider'
				),
				layouts: [ 'ticker' ],
			},
		],
	},
	{
		name: 'style',
		title: __( 'Style', 'ultimate-3d-testimonial-slider' ),
		fields: [
			{
				key: 'card_style',
				control: 'select',
				label: __( 'Card style', 'ultimate-3d-testimonial-slider' ),
				help: __(
					'Classic is the original card. Glass brings its own gradient backdrop; Dark suits dark sections.',
					'ultimate-3d-testimonial-slider'
				),
				options: {
					classic: __( 'Classic', 'ultimate-3d-testimonial-slider' ),
					modern: __(
						'Modern (soft shadow, photo beside name)',
						'ultimate-3d-testimonial-slider'
					),
					bubble: __(
						'Speech bubble',
						'ultimate-3d-testimonial-slider'
					),
					elegant: __(
						'Elegant (serif quote)',
						'ultimate-3d-testimonial-slider'
					),
					glass: __(
						'Glass (frosted)',
						'ultimate-3d-testimonial-slider'
					),
					dark: __( 'Dark', 'ultimate-3d-testimonial-slider' ),
					split: __(
						'Split (accent panel, photo on top)',
						'ultimate-3d-testimonial-slider'
					),
				},
			},
			{
				key: 'accent_color',
				control: 'color',
				label: __( 'Accent color', 'ultimate-3d-testimonial-slider' ),
				help: __(
					'Pagination dots and arrows, and the quote marks and lines of the Modern, Elegant and Dark styles.',
					'ultimate-3d-testimonial-slider'
				),
			},
			{
				key: 'text_color',
				control: 'color',
				label: __( 'Text color', 'ultimate-3d-testimonial-slider' ),
			},
			{
				key: 'background_color',
				control: 'color',
				label: __(
					'Card background',
					'ultimate-3d-testimonial-slider'
				),
			},
			{
				key: 'border_radius',
				control: 'range',
				label: __(
					'Card corner radius (px)',
					'ultimate-3d-testimonial-slider'
				),
			},
			{
				key: 'star_color',
				control: 'color',
				label: __( 'Star color', 'ultimate-3d-testimonial-slider' ),
				help: __(
					'Rating stars. Empty uses gold.',
					'ultimate-3d-testimonial-slider'
				),
			},
		],
	},
];
