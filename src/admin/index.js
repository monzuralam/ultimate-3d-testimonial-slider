/**
 * Admin: shortcode copy buttons, plus the admin styles (Tailwind + SCSS).
 */
import './tailwind.css';
import './main.scss';

const copyText = ( input ) => {
	input.select();

	if ( window.navigator.clipboard ) {
		return window.navigator.clipboard.writeText( input.value );
	}

	input.ownerDocument.execCommand( 'copy' );
	return Promise.resolve();
};

// Copy a shortcode and briefly show a check mark.
document.addEventListener( 'click', ( event ) => {
	const button = event.target.closest( '.uts-copy-shortcode' );
	const input = button && button.parentElement.querySelector( 'input' );

	if ( ! input ) {
		return;
	}

	const icon = button.querySelector( '.dashicons' );

	copyText( input ).then( () => {
		if ( ! icon ) {
			return;
		}

		icon.classList.replace( 'dashicons-admin-page', 'dashicons-yes' );
		setTimeout(
			() =>
				icon.classList.replace(
					'dashicons-yes',
					'dashicons-admin-page'
				),
			1500
		);
	} );
} );
