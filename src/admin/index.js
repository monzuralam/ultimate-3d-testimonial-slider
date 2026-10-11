/**
 * Admin: shortcode copy buttons, Getting Started preview tabs and video,
 * plus the admin styles (Tailwind + SCSS).
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

// Getting Started video: swap the thumbnail for the player on click.
document.addEventListener( 'click', ( event ) => {
	const button = event.target.closest( '[data-uts-video]' );

	if ( ! button ) {
		return;
	}

	const iframe = document.createElement( 'iframe' );
	iframe.src = `https://www.youtube-nocookie.com/embed/${ encodeURIComponent(
		button.dataset.utsVideo
	) }?autoplay=1&rel=0`;
	iframe.title = button.dataset.utsVideoTitle || '';
	iframe.allow =
		'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
	iframe.allowFullscreen = true;
	iframe.className = 'uts-video-frame';

	button.replaceWith( iframe );
	iframe.focus();
} );
