/**
 * Live preview: renders the shortcode on the server (POST /uts/v1/preview)
 * and starts the sliders in the returned markup.
 *
 * Requests are debounced and earlier ones are cancelled, so only the latest
 * attributes are shown.
 */
import { Spinner } from '@wordpress/components';
import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { preview } from '../api';

const destroySliders = ( root ) => {
	if ( ! root ) {
		return;
	}
	root.querySelectorAll( '.swiper' ).forEach( ( element ) => {
		if ( element.swiper ) {
			element.swiper.destroy( true, true );
		}
	} );
};

/**
 * @param {Object} props
 * @param {Object} props.attributes Shortcode attributes.
 * @param {number} props.delay      Debounce in ms. Default 300.
 */
const Preview = ( { attributes, delay = 300 } ) => {
	const [ html, setHtml ] = useState( '' );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ error, setError ] = useState( '' );
	const ref = useRef();
	const key = JSON.stringify( attributes );

	useEffect( () => {
		const controller =
			typeof window.AbortController === 'function'
				? new window.AbortController()
				: null;
		setIsLoading( true );

		const timer = setTimeout( () => {
			preview( attributes, controller ? controller.signal : undefined )
				.then( ( response ) => {
					destroySliders( ref.current );
					setHtml( response.html );
					setError( '' );
					setIsLoading( false );
				} )
				.catch( ( err ) => {
					if ( err && 'AbortError' === err.name ) {
						return;
					}
					setError(
						( err && err.message ) ||
							__(
								'The preview could not be loaded.',
								'ultimate-3d-testimonial-slider'
							)
					);
					setIsLoading( false );
				} );
		}, delay );

		return () => {
			clearTimeout( timer );
			if ( controller ) {
				controller.abort();
			}
		};
	}, [ key ] ); // eslint-disable-line react-hooks/exhaustive-deps

	useEffect( () => {
		if ( html && ref.current && window.utsSlider ) {
			window.utsSlider.init( ref.current );
		}
	}, [ html ] );

	useEffect( () => () => destroySliders( ref.current ), [] );

	const isEmpty = ! isLoading && ! error && -1 === html.indexOf( 'ucs-item' );

	return (
		<div
			className="uts-preview uts:relative uts:min-h-40 uts:rounded-md uts:bg-wp-bg uts:p-4 uts:md:p-8"
			aria-busy={ isLoading }
		>
			{ isLoading && (
				<div className="uts:absolute uts:inset-0 uts:z-10 uts:flex uts:items-center uts:justify-center">
					<Spinner />
				</div>
			) }
			{ error && (
				<p className="uts:m-0 uts:text-center uts:text-red-700">
					{ error }
				</p>
			) }
			<div
				ref={ ref }
				className={ isLoading ? 'uts:opacity-50' : '' }
				// Server-rendered shortcode output, escaped by UTS\Shortcode.
				dangerouslySetInnerHTML={ { __html: html } }
			/>
			{ isEmpty && (
				<p className="uts:m-0 uts:text-center uts:text-wp-muted">
					{ __(
						'No testimonials match these options.',
						'ultimate-3d-testimonial-slider'
					) }
				</p>
			) }
		</div>
	);
};

export default Preview;
