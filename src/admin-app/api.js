/**
 * apiFetch wrappers for the plugin's REST API (uts/v1) and core settings.
 */
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

const BASE = '/uts/v1';

/**
 * List saved shortcodes.
 *
 * @param {Object} query { status, search, page, per_page, orderby, order }.
 * @return {Promise<{items: Object[], total: number, totalPages: number}>} Result.
 */
export const listShortcodes = ( query ) =>
	apiFetch( {
		path: addQueryArgs( `${ BASE }/shortcodes`, query ),
		parse: false,
	} ).then( ( response ) =>
		response.json().then( ( items ) => ( {
			items,
			total: parseInt( response.headers.get( 'X-WP-Total' ), 10 ) || 0,
			totalPages:
				parseInt( response.headers.get( 'X-WP-TotalPages' ), 10 ) || 0,
		} ) )
	);

export const getShortcode = ( id ) =>
	apiFetch( { path: `${ BASE }/shortcodes/${ id }` } );

export const createShortcode = ( item ) =>
	apiFetch( { path: `${ BASE }/shortcodes`, method: 'POST', data: item } );

export const updateShortcode = ( id, item ) =>
	apiFetch( {
		path: `${ BASE }/shortcodes/${ id }`,
		method: 'PATCH',
		data: item,
	} );

export const trashShortcode = ( id ) =>
	apiFetch( { path: `${ BASE }/shortcodes/${ id }`, method: 'DELETE' } );

export const deleteShortcode = ( id ) =>
	apiFetch( {
		path: `${ BASE }/shortcodes/${ id }?force=true`,
		method: 'DELETE',
	} );

export const duplicateShortcode = ( id ) =>
	apiFetch( {
		path: `${ BASE }/shortcodes/${ id }/duplicate`,
		method: 'POST',
	} );

export const shortcodeUsage = ( id ) =>
	apiFetch( { path: `${ BASE }/shortcodes/${ id }/usage` } );

export const getStatus = () => apiFetch( { path: `${ BASE }/status` } );

/**
 * Render the shortcode for the given attributes.
 *
 * @param {Object}      attributes Shortcode attributes.
 * @param {AbortSignal} signal     Abort signal.
 * @return {Promise<{html: string}>} Rendered HTML.
 */
export const preview = ( attributes, signal ) =>
	apiFetch( {
		path: `${ BASE }/preview`,
		method: 'POST',
		data: { attributes },
		signal,
	} );

export const getSettings = () =>
	apiFetch( { path: '/wp/v2/settings' } ).then(
		( response ) => response.uts_settings
	);

export const saveSettings = ( settings ) =>
	apiFetch( {
		path: '/wp/v2/settings',
		method: 'POST',
		data: { uts_settings: settings },
	} ).then( ( response ) => response.uts_settings );
