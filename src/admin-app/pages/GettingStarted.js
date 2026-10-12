/**
 * Getting Started: welcome and video, live preview, setup checklist,
 * "How to use" guide, shortcode reference and resources.
 */
import { createInterpolateElement, useRef, useState } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';

import { useApp } from '../App';
import Card from '../components/Card';
import CopyField from '../components/CopyField';
import Preview from '../components/Preview';
import VideoEmbed from '../components/VideoEmbed';
import data from '../data';
import { isModifiedClick, routeUrl } from '../router';

const bold = { strong: <strong /> };
const strongCode = { strong: <strong />, code: <code /> };

const formatDefault = ( value ) => {
	if ( true === value ) {
		return 'true';
	}
	if ( false === value ) {
		return 'false';
	}
	if ( '' === value || null === value || undefined === value ) {
		return '—';
	}
	return String( value );
};

/**
 * Link that navigates inside the app (normal link on modified clicks).
 *
 * @param {Object}  props
 * @param {Object}  props.route     Route.
 * @param {string}  props.className Classes.
 * @param {Element} props.children  Content.
 */
const AppLink = ( { route, className, children } ) => {
	const { navigate } = useApp();
	return (
		<a
			href={ routeUrl( route ) }
			className={ className }
			onClick={ ( event ) => {
				if ( isModifiedClick( event ) ) {
					return;
				}
				event.preventDefault();
				navigate( route );
			} }
		>
			{ children }
		</a>
	);
};

const LAYOUTS = [
	{ value: 'slider', label: __( 'Slider', 'uts' ) },
	{ value: 'grid', label: __( 'Grid', 'uts' ) },
	{ value: 'list', label: __( 'List', 'uts' ) },
];

/** Slider / Grid / List tabs with arrow-key navigation (WAI-ARIA tabs). */
const LivePreview = () => {
	const [ layout, setLayout ] = useState( 'slider' );
	const tabs = useRef( [] );

	const onKeyDown = ( event, index ) => {
		const keys = {
			ArrowRight: 1,
			ArrowDown: 1,
			ArrowLeft: -1,
			ArrowUp: -1,
		};
		let next = null;
		if ( undefined !== keys[ event.key ] ) {
			next =
				( index + keys[ event.key ] + LAYOUTS.length ) % LAYOUTS.length;
		} else if ( 'Home' === event.key ) {
			next = 0;
		} else if ( 'End' === event.key ) {
			next = LAYOUTS.length - 1;
		}
		if ( null !== next ) {
			event.preventDefault();
			setLayout( LAYOUTS[ next ].value );
			tabs.current[ next ].focus();
		}
	};

	return (
		<Card className="uts:overflow-hidden">
			<div className="uts:mb-5 uts:flex uts:flex-wrap uts:items-center uts:justify-between uts:gap-3">
				<div>
					<h2 className="uts:m-0 uts:text-lg uts:font-semibold uts:text-gray-900">
						{ __( 'Live preview', 'uts' ) }
					</h2>
					<p className="uts:m-0 uts:mt-1 uts:text-wp-muted">
						{ __(
							'Your testimonials with your current settings.',
							'uts'
						) }
					</p>
				</div>
				{ data.status.published > 0 && (
					<div
						className="uts:inline-flex uts:rounded-md uts:border uts:border-wp-border uts:p-0.5"
						role="tablist"
						aria-label={ __( 'Preview layout', 'uts' ) }
					>
						{ LAYOUTS.map( ( item, index ) => {
							const selected = item.value === layout;
							return (
								<button
									key={ item.value }
									ref={ ( node ) =>
										( tabs.current[ index ] = node )
									}
									type="button"
									role="tab"
									id={ `uts-preview-tab-${ item.value }` }
									aria-selected={ selected }
									aria-controls="uts-preview-panel"
									tabIndex={ selected ? 0 : -1 }
									onClick={ () => setLayout( item.value ) }
									onKeyDown={ ( event ) =>
										onKeyDown( event, index )
									}
									className={ `uts:cursor-pointer uts:rounded uts:border-0 uts:px-4 uts:py-1.5 uts:text-sm uts:font-medium ${
										selected
											? 'uts:bg-wp-blue uts:text-white'
											: 'uts:bg-transparent uts:text-gray-700'
									}` }
								>
									{ item.label }
								</button>
							);
						} ) }
					</div>
				) }
			</div>

			{ data.status.published > 0 ? (
				<div
					id="uts-preview-panel"
					role="tabpanel"
					aria-labelledby={ `uts-preview-tab-${ layout }` }
				>
					<Preview
						attributes={ { type: layout, count: 6 } }
						delay={ 0 }
					/>
				</div>
			) : (
				<div className="uts:flex uts:flex-col uts:items-center uts:rounded-md uts:border uts:border-dashed uts:border-wp-border uts:bg-wp-bg uts:px-6 uts:py-12 uts:text-center">
					<span
						className="dashicons dashicons-format-quote uts:size-12 uts:text-5xl uts:text-wp-border"
						aria-hidden="true"
					/>
					<p className="uts:mt-3 uts:mb-4 uts:text-[15px] uts:text-wp-muted">
						{ __(
							'No testimonials yet. Add your first one to see the preview here.',
							'uts'
						) }
					</p>
					<a
						href={ data.links.newTestimonial }
						className="button button-primary"
					>
						{ __( 'Add Testimonial', 'uts' ) }
					</a>
				</div>
			) }
		</Card>
	);
};

const Checklist = () => {
	const { status, links, user } = data;
	const missing = status.published - status.with_photo;
	const savedSliders = status.shortcodes ? status.shortcodes.publish : 0;

	const items = [
		{
			done: status.published > 0,
			title: __( 'Add your testimonials', 'uts' ),
			text:
				status.published > 0
					? sprintf(
							/* translators: %s: number of testimonials */ _n(
								'%s testimonial published.',
								'%s testimonials published.',
								status.published,
								'uts'
							),
							status.published
						)
					: __(
							'Add at least one testimonial to show on your site.',
							'uts'
						),
			href: links.newTestimonial,
			link: __( 'Add Testimonial', 'uts' ),
		},
		{
			done: status.published > 0 && 0 === missing,
			title: __( 'Add client photos', 'uts' ),
			text:
				status.published > 0
					? sprintf(
							/* translators: 1: testimonials with a photo, 2: all testimonials */ __(
								'%1$s of %2$s testimonials have a photo. Without one, a quote icon is shown.',
								'uts'
							),
							status.with_photo,
							status.published
						)
					: __( 'Set a featured image on each testimonial.', 'uts' ),
			href: missing > 0 ? links.withoutPhoto : links.testimonials,
			link:
				missing > 0
					? sprintf(
							/* translators: %s: number of testimonials without a photo */ __(
								'Show the %s without a photo',
								'uts'
							),
							missing
						)
					: __( 'View Testimonials', 'uts' ),
		},
		{
			done: !! status.settings_saved,
			title: __( 'Choose your look', 'uts' ),
			text: status.settings_saved
				? __(
						'Settings saved. You can change them at any time.',
						'uts'
					)
				: __(
						'Pick the layout, slider effect, autoplay and colors. The defaults work too.',
						'uts'
					),
			route: user.canManage ? { page: 'uts-settings' } : null,
			link: __( 'Open Settings', 'uts' ),
		},
	];

	if ( user.canBuild ) {
		items.push( {
			done: savedSliders > 0,
			title: __( 'Save a slider (optional)', 'uts' ),
			text:
				savedSliders > 0
					? sprintf(
							/* translators: %s: number of saved sliders */ _n(
								'%s saved slider. Use it anywhere with its [uts id] shortcode.',
								'%s saved sliders. Use them anywhere with their [uts id] shortcodes.',
								savedSliders,
								'uts'
							),
							savedSliders
						)
					: __(
							'Save different looks (homepage, pricing page…) in the Shortcode Builder and reuse them by ID.',
							'uts'
						),
			route:
				savedSliders > 0
					? { page: 'uts-builder' }
					: { page: 'uts-builder', view: 'new' },
			link:
				savedSliders > 0
					? __( 'Open the Builder', 'uts' )
					: __( 'Create a Slider', 'uts' ),
		} );
	}

	items.push( {
		done: status.page_id > 0,
		title: __( 'Show them on a page', 'uts' ),
		text:
			status.page_id > 0
				? sprintf(
						/* translators: %s: page title */ __(
							'Used on "%s".',
							'uts'
						),
						links.usedOnTitle
					)
				: __(
						'Paste [uts] into any page. See "How to use" below.',
						'uts'
					),
		href: status.page_id > 0 ? links.usedOn : links.newPage,
		link:
			status.page_id > 0
				? __( 'View Page', 'uts' )
				: __( 'Create a Page', 'uts' ),
	} );

	const done = items.filter( ( item ) => item.done ).length;
	const allDone = done === items.length;
	const percent = Math.round( ( done / items.length ) * 100 );

	return (
		<Card>
			<details open={ ! allDone } className="uts-checklist">
				<summary
					className={ `uts:flex uts:flex-wrap uts:items-center uts:justify-between uts:gap-x-4 uts:gap-y-1 ${ allDone ? 'uts:cursor-pointer' : 'uts:pointer-events-none uts:list-none' }` }
				>
					{ allDone ? (
						<>
							<span className="uts:flex uts:items-center uts:gap-2 uts:text-[15px] uts:font-semibold uts:text-gray-900">
								<span
									className="dashicons dashicons-yes-alt uts:size-6 uts:text-2xl uts:text-green-600"
									aria-hidden="true"
								/>
								{ __(
									'All set! Your testimonials are live.',
									'uts'
								) }
							</span>
							<span className="uts:text-sm uts:font-medium uts:text-wp-blue">
								<span className="uts-checklist__show">
									{ __( 'Show checklist', 'uts' ) }
								</span>
								<span className="uts-checklist__hide">
									{ __( 'Hide checklist', 'uts' ) }
								</span>
							</span>
						</>
					) : (
						<>
							<h2 className="uts:m-0 uts:text-lg uts:font-semibold uts:text-gray-900">
								{ __( 'Setup checklist', 'uts' ) }
							</h2>
							<span className="uts:text-sm uts:font-medium uts:text-wp-muted">
								{ sprintf(
									/* translators: 1: done steps, 2: all steps */ __(
										'%1$s of %2$s done',
										'uts'
									),
									done,
									items.length
								) }
							</span>
						</>
					) }
				</summary>
				{ ! allDone && (
					<div
						className="uts:mt-4 uts:mb-2 uts:h-2 uts:overflow-hidden uts:rounded-full uts:bg-wp-bg"
						role="progressbar"
						aria-valuemin={ 0 }
						aria-valuemax={ items.length }
						aria-valuenow={ done }
						aria-label={ __( 'Setup progress', 'uts' ) }
					>
						<div
							className="uts:h-full uts:rounded-full uts:bg-green-600"
							style={ { width: `${ percent }%` } }
						/>
					</div>
				) }
				<ol className="uts:m-0 uts:mt-2 uts:flex uts:list-none uts:flex-col uts:divide-y uts:divide-wp-bg uts:p-0">
					{ items.map( ( item ) => {
						const className = `button ${ item.done ? '' : 'button-primary' }`;
						return (
							<li
								key={ item.title }
								className="uts:m-0 uts:flex uts:flex-wrap uts:items-center uts:gap-x-4 uts:gap-y-2 uts:py-3"
							>
								<span
									className={ `dashicons ${ item.done ? 'dashicons-yes-alt uts:text-green-600' : 'dashicons-marker uts:text-wp-border' } uts:size-6 uts:text-2xl` }
									aria-hidden="true"
								/>
								<div className="uts:min-w-48 uts:flex-1">
									<p className="uts:m-0 uts:flex uts:flex-wrap uts:items-center uts:gap-2 uts:font-semibold uts:text-gray-900">
										{ item.title }
										{ item.done ? (
											<span className="uts:rounded-full uts:bg-green-50 uts:px-2 uts:py-0.5 uts:text-xs uts:font-medium uts:text-green-700">
												{ __( 'Done', 'uts' ) }
											</span>
										) : (
											<span className="screen-reader-text">
												{ __( '(to do)', 'uts' ) }
											</span>
										) }
									</p>
									<p className="uts:m-0 uts:text-wp-muted">
										{ item.text }
									</p>
								</div>
								{ item.route && (
									<AppLink
										route={ item.route }
										className={ className }
									>
										{ item.link }
									</AppLink>
								) }
								{ item.href && (
									<a
										className={ className }
										href={ item.href }
									>
										{ item.link }
									</a>
								) }
							</li>
						);
					} ) }
				</ol>
			</details>
		</Card>
	);
};

const Step = ( { number, title, open = false, children } ) => (
	<details
		className="uts:group uts:rounded-md uts:border uts:border-wp-border"
		open={ open }
	>
		<summary className="uts:flex uts:cursor-pointer uts:items-center uts:gap-3 uts:p-4 uts:text-[15px] uts:font-semibold uts:text-gray-900">
			<span className="uts:flex uts:size-7 uts:shrink-0 uts:items-center uts:justify-center uts:rounded-full uts:bg-wp-blue uts:text-sm uts:text-white">
				{ number }
			</span>
			{ title }
		</summary>
		<div className="uts:border-t uts:border-wp-border uts:px-4 uts:pb-4 uts:md:pl-4 uts:text-[14px]">
			{ children }
		</div>
	</details>
);

const EXAMPLES = [
	[ '[uts type="grid" grid_columns="4"]', __( 'A 4-column grid.', 'uts' ) ],
	[ '[uts type="list" count="3"]', __( 'A list of the first 3.', 'uts' ) ],
	[
		'[uts effect="fade" autoplay="true"]',
		__( 'Fading slider that plays by itself.', 'uts' ),
	],
	[
		'[uts navigation="true" pagination="false"]',
		__( 'Arrows instead of dots.', 'uts' ),
	],
	[
		'[uts effect="cards" loop="true"]',
		__( 'Stacked cards that loop.', 'uts' ),
	],
	[
		'[uts show_image="false" excerpt_length="0"]',
		__( 'Full text, no photos.', 'uts' ),
	],
];

const PLACES = [
	[
		'dashicons-block-default',
		__( 'Block editor (Gutenberg)', 'uts' ),
		__(
			'Click +, search for "Shortcode", add the Shortcode block and paste [uts].',
			'uts'
		),
	],
	[
		'dashicons-edit-page',
		__( 'Classic editor', 'uts' ),
		__( 'Paste [uts] on its own line in the content.', 'uts' ),
	],
	[
		'dashicons-layout',
		__( 'Elementor and other page builders', 'uts' ),
		__( 'Drag in the "Shortcode" widget/element and paste [uts].', 'uts' ),
	],
	[
		'dashicons-welcome-widgets-menus',
		__( 'Sidebar or footer widget', 'uts' ),
		__(
			'Appearance → Widgets (or Editor for block themes): add a Shortcode block and paste [uts].',
			'uts'
		),
	],
];

const TIPS = [
	[
		__( 'Nothing shows up on the page', 'uts' ),
		__(
			'Check that the testimonials are published, that the category slug is spelled exactly right, and that the shortcode is not inside a code block. A saved slider must be published, not a draft. Clear your caching plugin after changes.',
			'uts'
		),
	],
	[
		__( 'More than one slider on a page', 'uts' ),
		__(
			'Supported. Each [uts] works on its own with its own attributes.',
			'uts'
		),
	],
	[
		__( 'Changing the order', 'uts' ),
		__(
			'Set "Order" in the Page Attributes box of each testimonial. Lower numbers come first.',
			'uts'
		),
	],
	[
		__( 'Autoplay or loop looks odd with few testimonials', 'uts' ),
		__(
			'Loop needs at least 3 testimonials to look smooth; add more or turn loop off.',
			'uts'
		),
	],
	[
		__( 'Matching my theme', 'uts' ),
		__(
			"Use the Style tab in Settings for colors and corner radius. For more, target .ucs-container and .ucs-item in your theme's custom CSS.",
			'uts'
		),
	],
];

const Guide = () => {
	const rows = [ 'id', ...Object.keys( data.attributeDocs ) ].filter(
		( key ) => 'id' === key || 'category' === key || data.schema[ key ]
	);
	const docs = {
		...data.attributeDocs,
		id: __(
			'Use a saved slider from the Shortcode Builder; other attributes still override it.',
			'uts'
		),
	};

	return (
		<Card title={ __( 'How to use', 'uts' ) }>
			<div className="uts-guide uts:flex uts:flex-col uts:gap-3">
				<Step
					number="1"
					title={ __( 'Add a testimonial', 'uts' ) }
					open
				>
					<ol className="uts:ml-4 uts:list-decimal uts:space-y-2">
						<li>
							{ createInterpolateElement(
								__(
									'Go to <strong>Ultimate 3D Testimonial Slider → Add Post</strong>.',
									'uts'
								),
								bold
							) }
						</li>
						<li>
							{ createInterpolateElement(
								__(
									'<strong>Title:</strong> the client\'s name, e.g. "Jane Cooper".',
									'uts'
								),
								bold
							) }
						</li>
						<li>
							{ createInterpolateElement(
								__(
									'<strong>Content:</strong> what the client said. Keep it short; the excerpt length in Settings trims long ones.',
									'uts'
								),
								bold
							) }
						</li>
						<li>
							{ createInterpolateElement(
								__(
									"<strong>Client Featured Image:</strong> the client's photo. A square image of at least 160 × 160 px looks best, since it is shown as a circle.",
									'uts'
								),
								bold
							) }
						</li>
						<li>
							{ createInterpolateElement(
								__(
									'<strong>Position & Company Name</strong> (below the editor): e.g. "CEO, Acme Inc."',
									'uts'
								),
								bold
							) }
						</li>
						<li>
							{ createInterpolateElement(
								__(
									'<strong>Order</strong> (Page Attributes box): lower numbers show first. Leave it at 0 to keep the default order.',
									'uts'
								),
								bold
							) }
						</li>
						<li>
							{ createInterpolateElement(
								__(
									'Click <strong>Publish</strong>. Only published testimonials are shown.',
									'uts'
								),
								bold
							) }
						</li>
					</ol>
				</Step>

				<Step
					number="2"
					title={ __( 'Organize with categories (optional)', 'uts' ) }
				>
					<p>
						{ __(
							'Categories let you show different testimonials in different places, for example "Web Design" clients on one page and "SEO" clients on another.',
							'uts'
						) }
					</p>
					<ol className="uts:ml-4 uts:list-decimal uts:space-y-2">
						<li>
							{ createInterpolateElement(
								__(
									'Create categories under <strong>Category</strong> in the plugin menu, or tick them in the Category box while editing a testimonial.',
									'uts'
								),
								bold
							) }
						</li>
						<li>
							{ createInterpolateElement(
								__(
									'Note the category <strong>slug</strong> (the URL-friendly name, e.g. <code>web-design</code>) on the Category screen.',
									'uts'
								),
								strongCode
							) }
						</li>
						<li>{ __( 'Use it in the shortcode:', 'uts' ) }</li>
					</ol>
					<CopyField
						value='[uts category="web-design"]'
						className="uts:mt-2 uts:max-w-md"
					/>
				</Step>

				<Step
					number="3"
					title={ __( 'Show testimonials on your site', 'uts' ) }
				>
					<p>
						{ __(
							'Everything works with one shortcode. Copy it, then paste it where you want the testimonials:',
							'uts'
						) }
					</p>
					<CopyField
						value="[uts]"
						className="uts:mb-4 uts:max-w-md"
					/>
					<div className="uts:grid uts:grid-cols-1 uts:gap-3 uts:md:grid-cols-2">
						{ PLACES.map( ( [ icon, title, text ] ) => (
							<div
								key={ title }
								className="uts:rounded-md uts:bg-wp-bg uts:p-4"
							>
								<p className="uts:m-0 uts:flex uts:items-center uts:gap-2 uts:font-semibold uts:text-gray-900">
									<span
										className={ `dashicons ${ icon } uts:text-wp-blue` }
										aria-hidden="true"
									/>
									{ title }
								</p>
								<p className="uts:m-0 uts:mt-1 uts:text-wp-muted">
									{ text }
								</p>
							</div>
						) ) }
					</div>
					<p className="uts:mt-4 uts:mb-1">
						{ __(
							'In a theme template file (for developers):',
							'uts'
						) }
					</p>
					<pre className="uts:m-0 uts:overflow-x-auto uts:rounded-md uts:bg-gray-900 uts:p-3 uts:text-[13px] uts:text-gray-100">
						<code>
							{ "<?php echo do_shortcode( '[uts]' ); ?>" }
						</code>
					</pre>
				</Step>

				<Step number="4" title={ __( 'Customize the design', 'uts' ) }>
					<p>
						{ __(
							'There are three levels, and you can mix them:',
							'uts'
						) }
					</p>
					<ul className="uts:ml-4 uts:list-disc uts:space-y-2">
						<li>
							{ createInterpolateElement(
								__(
									'<strong>Settings page</strong>: the defaults for every [uts] on your site, such as layout, effect, autoplay and colors.',
									'uts'
								),
								bold
							) }
						</li>
						<li>
							{ createInterpolateElement(
								__(
									'<strong>Shortcode Builder</strong>: save a named slider (e.g. "Homepage") that changes some options, and use it anywhere with its own shortcode, like [uts id="5"]. Editing it updates every page that uses it.',
									'uts'
								),
								bold
							) }
						</li>
						<li>
							{ createInterpolateElement(
								__(
									'<strong>Shortcode attributes</strong>: change one slider only. Any setting can be used as an attribute with the same name, and it wins over everything else.',
									'uts'
								),
								bold
							) }
						</li>
					</ul>
					<p>{ __( 'Examples:', 'uts' ) }</p>
					<div className="uts:grid uts:grid-cols-1 uts:gap-x-6 uts:gap-y-3 uts:lg:grid-cols-2">
						{ EXAMPLES.map( ( [ shortcode, text ] ) => (
							<div key={ shortcode }>
								<CopyField value={ shortcode } />
								<p className="uts:m-0 uts:mt-1 uts:text-wp-muted">
									{ text }
								</p>
							</div>
						) ) }
					</div>
				</Step>

				<Step number="5" title={ __( 'Shortcode reference', 'uts' ) }>
					<div className="uts:overflow-x-auto uts:pt-4">
						<table className="widefat striped uts:min-w-[560px]">
							<thead>
								<tr>
									<th scope="col">
										{ __( 'Attribute', 'uts' ) }
									</th>
									<th scope="col">
										{ __( 'What it does', 'uts' ) }
									</th>
									<th scope="col">
										{ __( 'Default', 'uts' ) }
									</th>
								</tr>
							</thead>
							<tbody>
								{ rows.map( ( key ) => (
									<tr key={ key }>
										<td>
											<code>{ key }</code>
										</td>
										<td>{ docs[ key ] }</td>
										<td>
											<code>
												{ formatDefault(
													'id' === key ||
														'category' === key
														? ''
														: data.settings[ key ]
												) }
											</code>
										</td>
									</tr>
								) ) }
							</tbody>
						</table>
						<p className="uts:mb-0 uts:text-wp-muted">
							{ __(
								'"Default" shows your current Settings value, used when the attribute is left out.',
								'uts'
							) }
						</p>
					</div>
				</Step>

				<Step
					number="?"
					title={ __( 'Tips & troubleshooting', 'uts' ) }
				>
					<dl className="uts:m-0 uts:mt-4 uts:space-y-3">
						{ TIPS.map( ( [ title, text ] ) => (
							<div key={ title }>
								<dt className="uts:font-semibold uts:text-gray-900">
									{ title }
								</dt>
								<dd className="uts:m-0 uts:text-wp-muted">
									{ text }
								</dd>
							</div>
						) ) }
					</dl>
				</Step>
			</div>
		</Card>
	);
};

const RESOURCES = [
	[
		'dashicons-book',
		__( 'Documentation', 'uts' ),
		__(
			'Features, FAQ and the full shortcode reference on WordPress.org.',
			'uts'
		),
		'docs',
		__( 'Read the Docs', 'uts' ),
	],
	[
		'dashicons-sos',
		__( 'Need help?', 'uts' ),
		__(
			'Ask a question in the free support forum. We usually reply within a few days.',
			'uts'
		),
		'support',
		__( 'Open Support Forum', 'uts' ),
	],
	[
		'dashicons-star-filled',
		__( 'Enjoying the plugin?', 'uts' ),
		__(
			'A quick review on WordPress.org helps other people find it and keeps it improving.',
			'uts'
		),
		'review',
		__( 'Leave a Review', 'uts' ),
	],
	[
		'dashicons-editor-code',
		__( 'Source code', 'uts' ),
		__( 'Report bugs, suggest features or contribute on GitHub.', 'uts' ),
		'github',
		__( 'View on GitHub', 'uts' ),
	],
];

const GettingStarted = () => {
	const { user, links } = data;

	return (
		<div className="uts-getting-started">
			<h1 className="screen-reader-text">
				{ __( 'Getting Started', 'uts' ) }
			</h1>

			<div className="uts:grid uts:grid-cols-1 uts:gap-6 uts:xl:grid-cols-[minmax(0,1fr)_320px]">
				<div className="uts:flex uts:min-w-0 uts:flex-col uts:gap-6">
					<Card>
						<p className="uts:m-0 uts:text-[28px] uts:leading-tight uts:font-semibold uts:text-gray-900">
							{ sprintf(
								/* translators: %s: user display name */ __(
									'Welcome, %s',
									'uts'
								),
								user.name
							) }{ ' ' }
							👋
						</p>
						<p className="uts:mt-3 uts:mb-0 uts:max-w-2xl uts:text-[15px] uts:text-wp-muted">
							{ __(
								'Turn what your clients say into a 3D slider, grid or list. Add testimonials, place one shortcode, and you are done.',
								'uts'
							) }
						</p>
						<div className="uts:mt-5 uts:flex uts:flex-wrap uts:gap-3">
							<a
								href={ links.newTestimonial }
								className="button button-primary button-hero uts:inline-flex! uts:items-center uts:gap-1"
							>
								<span
									className="dashicons dashicons-plus-alt2 uts:size-5 uts:text-xl uts:leading-none"
									aria-hidden="true"
								/>
								{ __( 'Add Testimonial', 'uts' ) }
							</a>
							{ user.canBuild && (
								<AppLink
									route={ {
										page: 'uts-builder',
										view: 'new',
									} }
									className="button button-hero uts:inline-flex! uts:items-center uts:gap-1"
								>
									<span
										className="dashicons dashicons-slides uts:size-5 uts:text-xl uts:leading-none"
										aria-hidden="true"
									/>
									{ __( 'Build a Slider', 'uts' ) }
								</AppLink>
							) }
							{ user.canManage && (
								<AppLink
									route={ { page: 'uts-settings' } }
									className="button button-hero uts:inline-flex! uts:items-center uts:gap-1"
								>
									<span
										className="dashicons dashicons-admin-appearance uts:size-5 uts:text-xl uts:leading-none"
										aria-hidden="true"
									/>
									{ __( 'Customize the Look', 'uts' ) }
								</AppLink>
							) }
						</div>
						<VideoEmbed videoId={ data.videoId } />
					</Card>

					<LivePreview />
					<Checklist />
					<Guide />
				</div>

				<aside className="uts:flex uts:flex-col uts:gap-6 uts:xl:sticky uts:xl:top-12 uts:xl:self-start">
					{ RESOURCES.map( ( [ icon, title, text, link, label ] ) => (
						<Card key={ link }>
							<p className="uts:m-0 uts:flex uts:items-center uts:gap-2 uts:text-[15px] uts:font-semibold uts:text-gray-900">
								<span
									className={ `dashicons ${ icon } uts:text-wp-blue` }
									aria-hidden="true"
								/>
								{ title }
							</p>
							<p className="uts:mt-2 uts:mb-4 uts:text-wp-muted">
								{ text }
							</p>
							<a
								className="button"
								href={ links[ link ] }
								target="_blank"
								rel="noopener noreferrer"
							>
								{ label }
								<span className="screen-reader-text">
									{ __( '(opens in a new tab)', 'uts' ) }
								</span>
							</a>
						</Card>
					) ) }
				</aside>
			</div>
		</div>
	);
};

export default GettingStarted;
