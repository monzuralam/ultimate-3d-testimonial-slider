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
		title: __( 'General', 'uts' ),
		fields: [
			{
				key: 'type',
				control: 'select',
				label: __( 'Default layout', 'uts' ),
				help: __(
					'Used when the shortcode has no type attribute.',
					'uts'
				),
				options: {
					slider: __( '3D slider', 'uts' ),
					grid: __( 'Grid', 'uts' ),
					list: __( 'List', 'uts' ),
					masonry: __( 'Masonry', 'uts' ),
					ticker: __( 'Ticker (scrolling row)', 'uts' ),
				},
			},
			{
				key: 'count',
				control: 'range',
				label: __( 'Testimonials to show', 'uts' ),
				help: __( '0 shows all testimonials.', 'uts' ),
			},
			{
				key: 'excerpt_length',
				control: 'range',
				label: __( 'Excerpt length (words)', 'uts' ),
				help: __( '0 shows the full testimonial.', 'uts' ),
			},
			{
				key: 'image_size',
				control: 'select',
				label: __( 'Image size', 'uts' ),
				options: {
					thumbnail: __( 'Thumbnail', 'uts' ),
					medium: __( 'Medium', 'uts' ),
					large: __( 'Large', 'uts' ),
					full: __( 'Full', 'uts' ),
				},
			},
		],
	},
	{
		name: 'slider',
		title: __( 'Slider', 'uts' ),
		fields: [
			{
				key: 'effect',
				control: 'select',
				label: __( 'Effect', 'uts' ),
				options: {
					coverflow: __( '3D coverflow', 'uts' ),
					slide: __( 'Slide', 'uts' ),
					fade: __( 'Fade', 'uts' ),
					cards: __( 'Cards', 'uts' ),
					cube: __( '3D cube', 'uts' ),
					flip: __( 'Flip', 'uts' ),
					creative: __( 'Creative (slide & zoom)', 'uts' ),
				},
			},
			{
				key: 'speed',
				control: 'range',
				label: __( 'Transition speed (ms)', 'uts' ),
				step: 100,
			},
			{
				key: 'autoplay',
				control: 'toggle',
				label: __( 'Autoplay', 'uts' ),
			},
			{
				key: 'autoplay_delay',
				control: 'range',
				label: __( 'Autoplay delay (ms)', 'uts' ),
				step: 500,
				showIf: ( settings ) => settings.autoplay,
			},
			{
				key: 'loop',
				control: 'toggle',
				label: __( 'Loop', 'uts' ),
			},
			{
				key: 'pagination',
				control: 'toggle',
				label: __( 'Pagination dots', 'uts' ),
			},
			{
				key: 'navigation',
				control: 'toggle',
				label: __( 'Navigation arrows', 'uts' ),
			},
			{
				key: 'coverflow_rotate',
				control: 'range',
				label: __( 'Coverflow rotate (deg)', 'uts' ),
				showIf: isCoverflow,
			},
			{
				key: 'coverflow_depth',
				control: 'range',
				label: __( 'Coverflow depth', 'uts' ),
				showIf: isCoverflow,
			},
			{
				key: 'coverflow_stretch',
				control: 'range',
				label: __( 'Coverflow stretch', 'uts' ),
				showIf: isCoverflow,
			},
		],
	},
	{
		name: 'display',
		title: __( 'Display', 'uts' ),
		fields: [
			{
				key: 'show_image',
				control: 'toggle',
				label: __( 'Show image', 'uts' ),
			},
			{
				key: 'show_title',
				control: 'toggle',
				label: __( 'Show client name', 'uts' ),
			},
			{
				key: 'show_designation',
				control: 'toggle',
				label: __( 'Show designation', 'uts' ),
			},
			{
				key: 'grid_columns',
				control: 'range',
				label: __( 'Columns', 'uts' ),
				help: __(
					'Desktop columns for the grid and masonry layouts. Smaller screens use fewer.',
					'uts'
				),
				layouts: [ 'grid', 'masonry' ],
			},
			{
				key: 'ticker_duration',
				control: 'range',
				label: __( 'Ticker speed (seconds per testimonial)', 'uts' ),
				help: __(
					'Higher is slower. The ticker pauses on hover and stays still for visitors who prefer reduced motion.',
					'uts'
				),
				layouts: [ 'ticker' ],
			},
		],
	},
	{
		name: 'style',
		title: __( 'Style', 'uts' ),
		fields: [
			{
				key: 'accent_color',
				control: 'color',
				label: __( 'Accent color', 'uts' ),
				help: __( 'Pagination dots and arrows.', 'uts' ),
			},
			{
				key: 'text_color',
				control: 'color',
				label: __( 'Text color', 'uts' ),
			},
			{
				key: 'background_color',
				control: 'color',
				label: __( 'Card background', 'uts' ),
			},
			{
				key: 'border_radius',
				control: 'range',
				label: __( 'Card corner radius (px)', 'uts' ),
			},
		],
	},
];
