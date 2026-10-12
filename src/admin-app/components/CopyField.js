/**
 * Read-only shortcode field with a copy button.
 */
import { Button } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const copy = ( text, input ) => {
	if ( window.navigator.clipboard && window.isSecureContext ) {
		return window.navigator.clipboard.writeText( text );
	}

	// Fallback for http:// admin pages, where the Clipboard API is unavailable.
	input.select();
	input.ownerDocument.execCommand( 'copy' );
	return Promise.resolve();
};

/**
 * @param {Object} props
 * @param {string} props.value     Shortcode.
 * @param {string} props.className Extra classes on the wrapper.
 */
const CopyField = ( { value, className = '' } ) => {
	const [ copied, setCopied ] = useState( false );
	let input = null;

	const onCopy = () =>
		copy( value, input ).then( () => {
			setCopied( true );
			setTimeout( () => setCopied( false ), 1500 );
		} );

	return (
		<div
			className={ `uts:flex uts:items-center uts:gap-1.5 ${ className }` }
		>
			<input
				ref={ ( node ) => ( input = node ) }
				type="text"
				readOnly
				value={ value }
				aria-label={ __( 'Shortcode', 'uts' ) }
				className="uts:min-w-0 uts:flex-1 uts:font-mono uts:text-[13px]"
				onFocus={ ( event ) => event.target.select() }
			/>
			<Button
				variant="secondary"
				onClick={ onCopy }
				label={
					copied
						? __( 'Copied', 'uts' )
						: __( 'Copy shortcode', 'uts' )
				}
				showTooltip
			>
				<span
					className={ `dashicons ${ copied ? 'dashicons-yes' : 'dashicons-admin-page' }` }
					aria-hidden="true"
				/>
			</Button>
			<span className="screen-reader-text" aria-live="polite">
				{ copied ? __( 'Shortcode copied.', 'uts' ) : '' }
			</span>
		</div>
	);
};

export default CopyField;
