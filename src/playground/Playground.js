/**
 * Shortcode playground: pick options, see the real output rendered by the
 * server (/uts/v1/preview), and copy the matching shortcode.
 */
import apiFetch from '@wordpress/api-fetch';
import { Button, Notice, SelectControl, Spinner } from '@wordpress/components';
import { useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import Field from '../settings/components/Field';
import { TABS } from '../settings/fields';
import { buildShortcode } from './shortcode';

const { schema, settings, categories, canManage } = window.utsPlaygroundData;

// Settings-page field definitions (labels, control type), by key.
const FIELDS = TABS.flatMap( ( tab ) => tab.fields ).reduce(
	( all, field ) => ( { ...all, [ field.key ]: field } ),
	{}
);

const isSlider = ( values ) => 'slider' === values.type;

// Controls shown in the playground, in display (and shortcode) order.
const CONTROLS = [
	{ key: 'type', label: __( 'Layout', 'uts' ) },
	{ key: 'effect', showIf: isSlider },
	{ key: 'category' },
	{ key: 'count' },
	{ key: 'grid_columns', showIf: ( values ) => 'grid' === values.type },
	{ key: 'autoplay', showIf: isSlider },
	{ key: 'navigation', showIf: isSlider },
	{ key: 'pagination', showIf: isSlider },
	{ key: 'loop', showIf: isSlider },
];

const initialValues = ( saved ) => ( { ...saved, category: '' } );

const destroySliders = ( root ) =>
	root &&
	root.querySelectorAll( '.swiper' ).forEach( ( element ) => {
		if ( element.swiper ) {
			element.swiper.destroy( true, true );
		}
	} );

const Playground = () => {
	const [ saved, setSaved ] = useState( settings );
	const [ values, setValues ] = useState( () => initialValues( settings ) );
	const [ html, setHtml ] = useState( '' );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ copied, setCopied ] = useState( false );
	const [ notice, setNotice ] = useState( null );
	const [ isSaving, setIsSaving ] = useState( false );
	const previewRef = useRef();

	const visible = CONTROLS.filter(
		( control ) => ! control.showIf || control.showIf( values )
	);
	const visibleKeys = visible.map( ( control ) => control.key );

	// Only send what is visible, so hidden options fall back to the settings.
	const attributes = useMemo( () => {
		const result = {};
		visibleKeys.forEach( ( key ) => ( result[ key ] = values[ key ] ) );
		return result;
	}, [ values, visibleKeys.join() ] ); // eslint-disable-line react-hooks/exhaustive-deps

	const shortcode = buildShortcode( values, saved, visibleKeys );
	const isChanged = shortcode !== '[uts]';

	// Render the preview on the server (debounced).
	useEffect( () => {
		const controller = new window.AbortController();
		setIsLoading( true );

		const timer = setTimeout( () => {
			apiFetch( {
				path: '/uts/v1/preview',
				method: 'POST',
				data: { attributes },
				signal: controller.signal,
			} )
				.then( ( response ) => {
					destroySliders( previewRef.current );
					setHtml( response.html );
					setIsLoading( false );
				} )
				.catch( ( error ) => {
					if ( 'AbortError' !== error.name ) {
						setNotice( {
							status: 'error',
							message: error.message,
						} );
						setIsLoading( false );
					}
				} );
		}, 300 );

		return () => {
			clearTimeout( timer );
			controller.abort();
		};
	}, [ attributes ] );

	// Start the sliders in the new markup.
	useEffect( () => {
		if ( html && previewRef.current && window.utsSlider ) {
			window.utsSlider.init( previewRef.current );
		}
	}, [ html ] );

	useEffect( () => () => destroySliders( previewRef.current ), [] );

	const update = ( key, value ) =>
		setValues( ( current ) => ( { ...current, [ key ]: value } ) );

	const copy = () => {
		const done = () => {
			setCopied( true );
			setTimeout( () => setCopied( false ), 1500 );
		};

		if ( window.navigator.clipboard ) {
			window.navigator.clipboard.writeText( shortcode ).then( done );
		}
	};

	// Save the playground's options (except category) as the global defaults.
	const saveAsDefaults = () => {
		const next = { ...saved };
		visibleKeys.forEach( ( key ) => {
			if ( 'category' !== key ) {
				next[ key ] = values[ key ];
			}
		} );

		setIsSaving( true );
		setNotice( null );

		apiFetch( {
			path: '/wp/v2/settings',
			method: 'POST',
			data: { uts_settings: next },
		} )
			.then( ( response ) => {
				setSaved( response.uts_settings );
				setNotice( {
					status: 'success',
					message: __(
						'Saved as your defaults. Every [uts] on your site now uses these options.',
						'uts'
					),
				} );
			} )
			.catch( ( error ) =>
				setNotice( { status: 'error', message: error.message } )
			)
			.finally( () => setIsSaving( false ) );
	};

	const renderControl = ( { key, label } ) => {
		if ( 'category' === key ) {
			return (
				<SelectControl
					key={ key }
					label={ __( 'Category', 'uts' ) }
					value={ values.category }
					options={ [
						{ value: '', label: __( 'All categories', 'uts' ) },
						...categories,
					] }
					onChange={ ( value ) => update( 'category', value ) }
					__next40pxDefaultSize
					__nextHasNoMarginBottom
				/>
			);
		}

		return (
			<Field
				key={ key }
				field={ {
					...FIELDS[ key ],
					label: label || FIELDS[ key ].label,
					help: undefined,
				} }
				schema={ schema[ key ] }
				value={ values[ key ] }
				onChange={ ( value ) => update( key, value ) }
			/>
		);
	};

	const selects = visible.filter(
		( { key } ) => 'toggle' !== ( FIELDS[ key ] || {} ).control
	);
	const toggles = visible.filter(
		( { key } ) => 'toggle' === ( FIELDS[ key ] || {} ).control
	);

	return (
		<div className="uts-playground">
			<div className="uts:grid uts:grid-cols-1 uts:gap-x-6 uts:gap-y-4 uts:sm:grid-cols-2 uts:2xl:grid-cols-4">
				{ selects.map( renderControl ) }
			</div>

			{ toggles.length > 0 && (
				<div className="uts:mt-4 uts:flex uts:flex-wrap uts:gap-x-8 uts:gap-y-3">
					{ toggles.map( renderControl ) }
				</div>
			) }

			<div
				className="uts:relative uts:mt-5 uts:min-h-40 uts:rounded-md uts:bg-wp-bg uts:p-4 uts:md:p-8"
				aria-busy={ isLoading }
				aria-live="polite"
			>
				{ isLoading && (
					<div className="uts:absolute uts:inset-0 uts:z-10 uts:flex uts:items-center uts:justify-center uts:bg-white/50">
						<Spinner />
					</div>
				) }
				<div
					ref={ previewRef }
					className={
						isLoading
							? 'uts:opacity-50 uts:transition-opacity'
							: 'uts:transition-opacity'
					}
					// Server-rendered shortcode output; escaped by uts_shortcode().
					dangerouslySetInnerHTML={ { __html: html } }
				/>
				{ ! isLoading && ! html.includes( 'ucs-item' ) && (
					<p className="uts:m-0 uts:text-center uts:text-wp-muted">
						{ __(
							'No testimonials match these options. Try another category.',
							'uts'
						) }
					</p>
				) }
			</div>

			<div className="uts:mt-5 uts:flex uts:flex-wrap uts:items-center uts:gap-3">
				<code
					className="uts:min-w-0 uts:flex-1 uts:rounded-md uts:border uts:border-wp-border uts:bg-white uts:px-3 uts:py-2.5 uts:font-mono uts:text-[13px] uts:break-all"
					aria-label={ __( 'Shortcode for these options', 'uts' ) }
				>
					{ shortcode }
				</code>
				<Button
					variant="primary"
					icon={ copied ? 'yes' : 'admin-page' }
					onClick={ copy }
					__next40pxDefaultSize
				>
					{ copied ? __( 'Copied!', 'uts' ) : __( 'Copy', 'uts' ) }
				</Button>
				<Button
					variant="secondary"
					disabled={ ! isChanged && ! values.category }
					onClick={ () => setValues( initialValues( saved ) ) }
					__next40pxDefaultSize
				>
					{ __( 'Start over', 'uts' ) }
				</Button>
				{ canManage && (
					<Button
						variant="tertiary"
						isBusy={ isSaving }
						disabled={
							isSaving ||
							shortcode ===
								buildShortcode( values, saved, [ 'category' ] )
						}
						onClick={ saveAsDefaults }
						__next40pxDefaultSize
					>
						{ __( 'Use as my defaults', 'uts' ) }
					</Button>
				) }
			</div>
			<p className="uts:mt-2 uts:mb-0 uts:text-wp-muted">
				{ __(
					'Paste the shortcode into any page. Options you did not change follow your Settings.',
					'uts'
				) }
			</p>

			{ notice && (
				<Notice
					className="uts:mt-4"
					status={ notice.status }
					onRemove={ () => setNotice( null ) }
				>
					{ notice.message }
				</Notice>
			) }
		</div>
	);
};

export default Playground;
