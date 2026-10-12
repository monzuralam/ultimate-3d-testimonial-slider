/**
 * Tiny router for the admin app.
 *
 * Routes live in the query string so every screen has a real admin URL
 * (bookmarks, reloads and middle-clicks work):
 *
 *   edit.php?post_type=uts&page=uts-builder&view=edit&id=5
 *
 * Switching screens uses history.pushState() and keeps the browser title and
 * the highlighted WordPress submenu item in sync.
 */
import data from './data';

const BASE = 'edit.php';

/**
 * Read the route from a URL's query string.
 *
 * @param {string} search location.search.
 * @return {{page: string, view: string, id: number}} Route.
 */
export const parseRoute = ( search ) => {
	const params = new window.URLSearchParams( search );
	const page = params.get( 'page' ) || 'uts-getting-started';
	const id = parseInt( params.get( 'id' ), 10 ) || 0;
	let view = params.get( 'view' ) || 'list';

	if ( 'uts-builder' === page && 'edit' === view && ! id ) {
		view = 'list';
	}

	return { page, view, id };
};

/**
 * URL for a route, relative to wp-admin.
 *
 * @param {Object} route Route.
 * @return {string} URL.
 */
export const routeUrl = ( route ) => {
	const params = new window.URLSearchParams( {
		post_type: 'uts',
		page: route.page,
	} );

	if ( 'uts-builder' === route.page && route.view && 'list' !== route.view ) {
		params.set( 'view', route.view );
		if ( route.id ) {
			params.set( 'id', String( route.id ) );
		}
	}

	return `${ BASE }?${ params.toString() }`;
};

/**
 * Update the browser title: "<Page title> ‹ <Site> — WordPress".
 *
 * @param {string} page Page slug.
 */
const syncTitle = ( page ) => {
	const info = data.pages && data.pages[ page ];
	if ( ! info ) {
		return;
	}

	const parts = document.title.split( ' ‹ ' );
	parts[ 0 ] = info.title;
	document.title = parts.join( ' ‹ ' );
};

/**
 * Highlight the matching item in the WordPress admin menu.
 *
 * @param {string} page Page slug.
 */
const syncMenu = ( page ) => {
	const links = document.querySelectorAll(
		'#adminmenu a[href*="post_type=uts&page=uts-"]'
	);

	links.forEach( ( link ) => {
		const isCurrent = link
			.getAttribute( 'href' )
			.endsWith( `page=${ page }` );
		const item = link.closest( 'li' );

		link.classList.toggle( 'current', isCurrent );
		item.classList.toggle( 'current', isCurrent );
		if ( isCurrent ) {
			link.setAttribute( 'aria-current', 'page' );
		} else {
			link.removeAttribute( 'aria-current' );
		}
	} );
};

/**
 * Apply a route to the browser: URL (push or replace), title and menu.
 *
 * @param {Object}  route   Route.
 * @param {boolean} replace Replace the current history entry instead of pushing.
 */
export const applyRoute = ( route, replace = false ) => {
	const url = routeUrl( route );

	if ( replace ) {
		window.history.replaceState( route, '', url );
	} else {
		window.history.pushState( route, '', url );
	}

	syncTitle( route.page );
	syncMenu( route.page );
};

/**
 * Whether a click should be left to the browser (new tab/window, etc.).
 *
 * @param {MouseEvent} event Click event.
 * @return {boolean} True when the app should not handle it.
 */
export const isModifiedClick = ( event ) =>
	event.defaultPrevented ||
	0 !== event.button ||
	event.metaKey ||
	event.ctrlKey ||
	event.shiftKey ||
	event.altKey;
