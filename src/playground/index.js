/**
 * Shortcode playground entry (Getting Started page).
 */
import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';

import Playground from './Playground';

domReady( () => {
	const root = document.getElementById( 'uts-playground-root' );

	if ( root ) {
		createRoot( root ).render( <Playground /> );
	}
} );
