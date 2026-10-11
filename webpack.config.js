const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		frontend: path.resolve( __dirname, 'src/frontend/index.js' ),
		admin: path.resolve( __dirname, 'src/admin/index.js' ),
	},
};
