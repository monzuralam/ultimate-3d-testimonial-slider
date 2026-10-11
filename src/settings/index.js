/**
 * Settings page entry.
 */
import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';

import App from './App';
import './main.scss';

domReady( () => {
	const root = document.getElementById( 'uts-settings-root' );

	if ( root ) {
		createRoot( root ).render( <App /> );
	}
} );
