/**
 * Build a [uts] shortcode containing only the values that differ from the
 * saved settings, so the shortcode stays short and follows future changes
 * to the other settings.
 *
 * @param {Object}   values Current playground values.
 * @param {Object}   saved  Saved global settings.
 * @param {string[]} keys   Keys to consider, in output order.
 * @return {string} Shortcode.
 */
export const buildShortcode = ( values, saved, keys ) => {
	const attributes = keys
		.filter( ( key ) =>
			'category' === key
				? !! values.category
				: values[ key ] !== saved[ key ]
		)
		.map( ( key ) => `${ key }="${ String( values[ key ] ) }"` );

	return attributes.length ? `[uts ${ attributes.join( ' ' ) }]` : '[uts]';
};
