/**
 * Shared header: plugin name, version and tabs. App pages switch in place;
 * the testimonial screens are normal links.
 */
import { __ } from '@wordpress/i18n';

import data from '../data';
import { isModifiedClick, routeUrl } from '../router';

/**
 * @param {Object}   props
 * @param {string}   props.page     Current page slug.
 * @param {Function} props.navigate navigate( route ).
 */
const Header = ( { page, navigate } ) => {
	// Order: All Testimonials | Categories | Getting Started | Shortcode Builder | Settings.
	const tabs = [
		{
			key: 'testimonials',
			label: __( 'All Testimonials', 'ultimate-3d-testimonial-slider' ),
			href: data.links.testimonials,
		},
	];
	if ( data.user.canCategories ) {
		tabs.push( {
			key: 'categories',
			label: __( 'Categories', 'ultimate-3d-testimonial-slider' ),
			href: data.links.categories,
		} );
	}

	[ 'uts-getting-started', 'uts-builder', 'uts-settings' ].forEach(
		( slug ) => {
			if ( data.pages[ slug ] ) {
				tabs.push( {
					key: slug,
					label:
						'uts-settings' === slug
							? __( 'Settings', 'ultimate-3d-testimonial-slider' )
							: data.pages[ slug ].title,
					href: routeUrl( { page: slug } ),
					route: { page: slug },
				} );
			}
		}
	);

	const onClick = ( event, tab ) => {
		if ( ! tab.route || isModifiedClick( event ) ) {
			return;
		}
		event.preventDefault();
		navigate( tab.route );
	};

	return (
		<div className="uts-admin-header uts:flex uts:flex-wrap uts:items-center uts:gap-x-8 uts:gap-y-1 uts:border-b uts:border-wp-border uts:bg-white uts:px-5 uts:md:px-8">
			<div className="uts:flex uts:items-center uts:gap-3 uts:py-3">
				<span
					className="dashicons dashicons-slides uts:text-wp-blue uts:text-[26px] uts:size-[26px]"
					aria-hidden="true"
				/>
				<span className="uts:text-lg uts:font-semibold uts:text-gray-900">
					{ __(
						'Ultimate 3D Testimonial Slider',
						'ultimate-3d-testimonial-slider'
					) }
				</span>
				<span className="uts:rounded-full uts:border uts:border-wp-border uts:px-2 uts:py-0.5 uts:text-xs uts:text-wp-muted">
					v{ data.version }
				</span>
			</div>
			<nav
				className="uts:flex uts:flex-1 uts:flex-wrap uts:gap-x-6"
				aria-label={ __(
					'Plugin pages',
					'ultimate-3d-testimonial-slider'
				) }
			>
				{ tabs.map( ( tab ) => {
					const active = tab.key === page;
					return (
						<a
							key={ tab.key }
							href={ tab.href }
							onClick={ ( event ) => onClick( event, tab ) }
							aria-current={ active ? 'page' : undefined }
							className={ `uts:-mb-px uts:border-b-2 uts:py-4 uts:text-sm uts:font-medium uts:no-underline ${
								active
									? 'uts:border-wp-blue uts:text-wp-blue'
									: 'uts:border-transparent uts:text-gray-700 uts:hover:text-wp-blue'
							}` }
						>
							{ tab.label }
						</a>
					);
				} ) }
			</nav>
			<a
				href={ data.links.support }
				target="_blank"
				rel="noopener noreferrer"
				className="uts:flex uts:items-center uts:gap-1 uts:py-3 uts:text-sm uts:font-medium uts:text-wp-blue uts:no-underline"
			>
				<span className="dashicons dashicons-sos" aria-hidden="true" />
				{ __( 'Get Help', 'ultimate-3d-testimonial-slider' ) }
				<span className="screen-reader-text">
					{ __(
						'(opens in a new tab)',
						'ultimate-3d-testimonial-slider'
					) }
				</span>
			</a>
		</div>
	);
};

export default Header;
