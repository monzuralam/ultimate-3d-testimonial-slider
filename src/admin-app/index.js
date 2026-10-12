/**
 * Admin app entry: Getting Started, Shortcode Builder and Settings.
 */
import domReady from '@wordpress/dom-ready';
import { createRoot, render } from '@wordpress/element';

import App from './App';
import './main.scss';

domReady( () => {
	const root = document.getElementById( 'uts-admin-root' );

	if ( ! root ) {
		return;
	}

	// createRoot() arrived with React 18 in WordPress 6.2; older versions only have render().
	if ( createRoot ) {
		createRoot( root ).render( <App /> );
	} else {
		render( <App />, root );
	}
} );
