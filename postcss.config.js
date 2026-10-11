/**
 * Replaces the @wordpress/scripts default PostCSS setup, so the WordPress
 * preset (autoprefixer etc.) is added back after Tailwind.
 */
module.exports = {
	plugins: [
		require( '@tailwindcss/postcss' ),
		...require( '@wordpress/postcss-plugins-preset' ),
	],
};
