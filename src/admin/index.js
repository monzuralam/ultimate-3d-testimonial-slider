/**
 * Admin: copies the shortcode from the shortcode-builder metabox.
 */
import './main.scss';

document.addEventListener( 'click', ( event ) => {
	const button = event.target.closest( '.uts-copy-shortcode' );

	if ( ! button ) {
		return;
	}

	const input = button.previousElementSibling;

	if ( ! input ) {
		return;
	}

	input.select();

	if ( window.navigator.clipboard ) {
		window.navigator.clipboard.writeText( input.value );
	} else {
		input.ownerDocument.execCommand( 'copy' );
	}
} );
