/**
 * Admin: shortcode copy buttons and the Getting Started live preview.
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

// Switch the live preview between slider, grid and list.
document.addEventListener( 'click', ( event ) => {
	const tab = event.target.closest( '[data-uts-preview]' );

	if ( ! tab ) {
		return;
	}

	const layout = tab.dataset.utsPreview;

	document
		.querySelectorAll( '[data-uts-preview]' )
		.forEach( ( item ) =>
			item.setAttribute(
				'aria-selected',
				String( item.dataset.utsPreview === layout )
			)
		);

	document
		.querySelectorAll( '[data-uts-preview-panel]' )
		.forEach( ( panel ) => {
			panel.hidden = panel.dataset.utsPreviewPanel !== layout;

			// Swiper measures on init; re-measure once the panel is visible.
			const slider = panel.querySelector( '.swiper' );
			if ( ! panel.hidden && slider && slider.swiper ) {
				slider.swiper.update();
			}
		} );
} );
