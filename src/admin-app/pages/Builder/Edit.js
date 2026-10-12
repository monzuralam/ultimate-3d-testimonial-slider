/**
 * Shortcode Builder editor: create or edit a saved slider.
 *
 * A slider stores only the options it changes; every other option follows
 * the global Settings. Each control shows the value in effect, marks the
 * options this slider changes, and can reset them to the global default.
 */
import {
	Button,
	Card as WPCard,
	CardBody,
	Notice,
	SelectControl,
	Spinner,
	TabPanel,
	TextControl,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

import { createShortcode, getShortcode, updateShortcode } from '../../api';
import { useApp } from '../../App';
import Card from '../../components/Card';
import CopyField from '../../components/CopyField';
import Field from '../../components/Field';
import Preview from '../../components/Preview';
import data from '../../data';
import { TABS } from '../../fields';
import { isModifiedClick, routeUrl } from '../../router';
import Usage from './Usage';

const EMPTY = { title: '', status: 'publish', settings: {} };

// Notice to show after a new slider is saved and the editor re-opens on its ID.
let carryNotice = null;

const display = ( value ) => {
	if ( true === value ) {
		return __( 'On', 'ultimate-3d-testimonial-slider' );
	}
	if ( false === value ) {
		return __( 'Off', 'ultimate-3d-testimonial-slider' );
	}
	if ( '' === value || null === value || undefined === value ) {
		return __( 'Theme default', 'ultimate-3d-testimonial-slider' );
	}
	return String( value );
};

const snapshot = ( item ) =>
	JSON.stringify( { title: item.title, settings: item.settings } );

/**
 * @param {Object} props
 * @param {number} props.id Saved shortcode ID, or 0 for a new slider.
 */
const Edit = ( { id } ) => {
	const { navigate, setDirty } = useApp();
	// New sliders start with the name the server would give them ("UTS #4").
	// Left as is, it's sent empty so the server names it after its real ID.
	const [ prefill ] = useState( () =>
		id ? '' : data.status?.next_title || ''
	);
	const [ item, setItem ] = useState(
		id ? null : { ...EMPTY, title: prefill, settings: {} }
	);
	const [ saved, setSaved ] = useState(
		id ? null : snapshot( { ...EMPTY, title: prefill } )
	);
	const [ notice, setNotice ] = useState( () => {
		const carried = carryNotice;
		carryNotice = null;
		return carried;
	} );
	const [ isSaving, setIsSaving ] = useState( false );
	const [ showUsage, setShowUsage ] = useState( false );

	useEffect( () => {
		if ( ! id ) {
			return;
		}
		getShortcode( id )
			.then( ( loaded ) => {
				const next = { ...loaded, settings: { ...loaded.settings } };
				setItem( next );
				setSaved( snapshot( next ) );
			} )
			.catch( ( error ) =>
				setNotice( {
					status: 'error',
					message: error.message,
					fatal: true,
				} )
			);
	}, [ id ] );

	const isDirty = !! item && snapshot( item ) !== saved;

	useEffect( () => {
		setDirty( isDirty );
	}, [ isDirty, setDirty ] );

	if ( ! item ) {
		return notice?.fatal ? (
			<>
				<Notice status="error" isDismissible={ false }>
					{ notice.message }
				</Notice>
				<p>
					<Button
						variant="link"
						onClick={ () => navigate( { page: 'uts-builder' } ) }
					>
						{ __(
							'← Back to all sliders',
							'ultimate-3d-testimonial-slider'
						) }
					</Button>
				</p>
			</>
		) : (
			<Spinner />
		);
	}

	const changed = item.settings;
	const effective = { ...data.settings, ...changed };
	delete effective.category;

	const setOption = ( key, value ) =>
		setItem( ( current ) => ( {
			...current,
			settings: { ...current.settings, [ key ]: value },
		} ) );

	const resetOption = ( key ) =>
		setItem( ( current ) => {
			const settings = { ...current.settings };
			delete settings[ key ];
			return { ...current, settings };
		} );

	const save = ( status ) => {
		setIsSaving( true );
		setNotice( null );

		const title = item.title.trim();
		const payload = {
			// Empty (or the untouched prefill) = "UTS #{id}", set by the server.
			title: ! id && title === prefill ? '' : title,
			status,
			settings: item.settings,
		};
		const request = id
			? updateShortcode( id, payload )
			: createShortcode( payload );

		request
			.then( ( stored ) => {
				const next = { ...stored, settings: { ...stored.settings } };
				setItem( next );
				setSaved( snapshot( next ) );
				setDirty( false );
				const message = {
					status: 'success',
					message:
						'publish' === stored.status
							? __(
									'Slider saved. Paste its shortcode into any page:',
									'ultimate-3d-testimonial-slider'
								)
							: __(
									'Draft saved. Publish it to use its shortcode on your site.',
									'ultimate-3d-testimonial-slider'
								),
					shortcode:
						'publish' === stored.status ? stored.shortcode : '',
				};
				setNotice( message );
				if ( ! id ) {
					carryNotice = message;
					navigate(
						{ page: 'uts-builder', view: 'edit', id: stored.id },
						{ replace: true, force: true }
					);
				}
			} )
			.catch( ( error ) =>
				setNotice( { status: 'error', message: error.message } )
			)
			.finally( () => setIsSaving( false ) );
	};

	const back = ( event ) => {
		if ( isModifiedClick( event ) ) {
			return;
		}
		event.preventDefault();
		navigate( { page: 'uts-builder' } );
	};

	const renderField = ( field ) => {
		const isChanged = Object.prototype.hasOwnProperty.call(
			changed,
			field.key
		);
		return (
			<div
				key={ field.key }
				className={ `uts-builder-field ${ isChanged ? 'is-changed' : '' }` }
			>
				<Field
					field={
						'type' === field.key
							? {
									...field,
									label: __(
										'Layout',
										'ultimate-3d-testimonial-slider'
									),
									help: undefined,
								}
							: field
					}
					schema={ data.schema[ field.key ] }
					value={ effective[ field.key ] }
					onChange={ ( value ) => setOption( field.key, value ) }
				/>
				{ isChanged && (
					<p className="uts:m-0 uts:mt-1.5 uts:flex uts:flex-wrap uts:items-center uts:gap-2 uts:text-[12px]">
						<span className="uts:rounded-full uts:bg-blue-50 uts:px-2 uts:py-0.5 uts:font-medium uts:text-wp-blue">
							{ __(
								'Changed',
								'ultimate-3d-testimonial-slider'
							) }
						</span>
						<Button
							variant="link"
							className="uts:text-[12px]!"
							onClick={ () => resetOption( field.key ) }
						>
							{ sprintf(
								/* translators: %s: global default value */ __(
									'Reset to default (%s)',
									'ultimate-3d-testimonial-slider'
								),
								display( data.settings[ field.key ] )
							) }
						</Button>
					</p>
				) }
			</div>
		);
	};

	// A saved slider has one layout, so options for the other layouts are
	// hidden here (unlike Settings, where the layout is only a default).
	// Changed values stay saved and come back when the layout is switched back.
	const isSlider = 'slider' === effective.type;
	const isVisible = ( field ) =>
		( ! field.layouts || field.layouts.includes( effective.type ) ) &&
		( ! field.showIf || field.showIf( effective ) );

	const renderTab = ( tab ) => (
		<div className="uts-settings__fields">
			{ 'general' === tab.name && (
				<SelectControl
					label={ __( 'Category', 'ultimate-3d-testimonial-slider' ) }
					help={ __(
						'Show only testimonials from one category.',
						'ultimate-3d-testimonial-slider'
					) }
					value={ changed.category || '' }
					options={ [
						{
							value: '',
							label: __(
								'All categories',
								'ultimate-3d-testimonial-slider'
							),
						},
						...data.categories.map( ( term ) => ( {
							value: term.value,
							label: `${ term.label } (${ term.count })`,
						} ) ),
					] }
					onChange={ ( value ) =>
						value
							? setOption( 'category', value )
							: resetOption( 'category' )
					}
					__next40pxDefaultSize
					__nextHasNoMarginBottom
				/>
			) }
			{ 'slider' === tab.name && ! isSlider ? (
				<p className="uts-builder-note uts:m-0 uts:rounded-md uts:bg-wp-bg uts:p-4 uts:text-wp-muted">
					{ __(
						'These options only apply to the Slider layout. Set Layout to "3D slider" on the General tab to change the effect, autoplay, arrows and dots.',
						'ultimate-3d-testimonial-slider'
					) }
				</p>
			) : (
				tab.fields.filter( isVisible ).map( renderField )
			) }
		</div>
	);

	const changedCount = Object.keys( changed ).length;

	return (
		<div className="uts-builder-edit">
			<h1 className="screen-reader-text">
				{ id
					? __( 'Edit Slider', 'ultimate-3d-testimonial-slider' )
					: __( 'New Slider', 'ultimate-3d-testimonial-slider' ) }
			</h1>

			<div className="uts-builder-bar uts:mb-6 uts:flex uts:flex-wrap uts:items-center uts:justify-between uts:gap-3 uts:rounded-lg uts:border uts:border-solid uts:border-wp-border uts:bg-white uts:p-3 uts:md:sticky uts:md:top-8 uts:md:z-10">
				<div className="uts:flex uts:min-w-0 uts:flex-1 uts:items-center uts:gap-3">
					<Button
						variant="secondary"
						href={ routeUrl( { page: 'uts-builder' } ) }
						onClick={ back }
					>
						{ __( '← Back', 'ultimate-3d-testimonial-slider' ) }
					</Button>
					<div className="uts:w-full uts:max-w-xs">
						<TextControl
							label={ __(
								'Slider name',
								'ultimate-3d-testimonial-slider'
							) }
							hideLabelFromVision
							placeholder={ __(
								'e.g. Homepage slider',
								'ultimate-3d-testimonial-slider'
							) }
							value={ item.title }
							maxLength={ 191 }
							onChange={ ( title ) =>
								setItem( ( current ) => ( {
									...current,
									title,
								} ) )
							}
							__next40pxDefaultSize
							__nextHasNoMarginBottom
						/>
					</div>
					{ id > 0 && (
						<span
							className={ `uts:rounded-full uts:px-2 uts:py-0.5 uts:text-xs uts:font-medium ${
								'publish' === item.status
									? 'uts:bg-green-100 uts:text-green-800'
									: 'uts:bg-wp-bg uts:text-wp-muted'
							}` }
						>
							{ 'publish' === item.status
								? __(
										'Published',
										'ultimate-3d-testimonial-slider'
									)
								: __(
										'Draft',
										'ultimate-3d-testimonial-slider'
									) }
						</span>
					) }
				</div>
				<div className="uts:flex uts:gap-2">
					<Button
						variant="secondary"
						disabled={ isSaving }
						onClick={ () => save( 'draft' ) }
					>
						{ 'publish' === item.status && id
							? __(
									'Switch to Draft',
									'ultimate-3d-testimonial-slider'
								)
							: __(
									'Save Draft',
									'ultimate-3d-testimonial-slider'
								) }
					</Button>
					<Button
						variant="primary"
						isBusy={ isSaving }
						disabled={
							isSaving ||
							( !! id && ! isDirty && 'publish' === item.status )
						}
						onClick={ () => save( 'publish' ) }
					>
						{ id && 'publish' !== item.status
							? __( 'Publish', 'ultimate-3d-testimonial-slider' )
							: __( 'Save', 'ultimate-3d-testimonial-slider' ) }
					</Button>
				</div>
			</div>

			{ notice && (
				<Notice
					status={ notice.status }
					onRemove={ () => setNotice( null ) }
				>
					{ notice.message }
					{ notice.shortcode && (
						<CopyField
							value={ notice.shortcode }
							className="uts:mt-2 uts:max-w-sm"
						/>
					) }
				</Notice>
			) }

			<div className="uts:grid uts:grid-cols-1 uts:gap-6 uts:xl:grid-cols-[420px_minmax(0,1fr)]">
				<div className="uts:flex uts:min-w-0 uts:flex-col uts:gap-6">
					<WPCard>
						<CardBody className="uts:pb-0!">
							<p className="uts:m-0 uts:text-wp-muted">
								{ changedCount
									? sprintf(
											/* translators: %s: number of options */ __(
												'This slider changes %s option(s). All others follow your Settings.',
												'ultimate-3d-testimonial-slider'
											),
											changedCount
										)
									: __(
											'This slider uses your Settings. Change any option below to make it different.',
											'ultimate-3d-testimonial-slider'
										) }
							</p>
						</CardBody>
						<TabPanel className="uts-settings__tabs" tabs={ TABS }>
							{ renderTab }
						</TabPanel>
					</WPCard>
				</div>

				<div className="uts:flex uts:min-w-0 uts:flex-col uts:gap-6 uts:xl:sticky uts:xl:top-28 uts:xl:self-start">
					<Card
						title={ __(
							'Live preview',
							'ultimate-3d-testimonial-slider'
						) }
					>
						<Preview attributes={ { ...changed } } />
					</Card>

					{ id > 0 && (
						<Card
							title={ __(
								'Shortcode',
								'ultimate-3d-testimonial-slider'
							) }
						>
							<CopyField
								value={ item.shortcode }
								className="uts:max-w-md"
							/>
							<p className="uts:mb-0 uts:text-wp-muted">
								{ 'publish' === item.status
									? __(
											'Paste it into any page. Attributes still override the slider, e.g. add autoplay="false".',
											'ultimate-3d-testimonial-slider'
										)
									: __(
											'Drafts show nothing on your site. Publish the slider to use this shortcode.',
											'ultimate-3d-testimonial-slider'
										) }
							</p>
							<div className="uts:mt-4">
								{ showUsage ? (
									<>
										<h3 className="uts:m-0 uts:mb-2 uts:text-sm">
											{ __(
												'Used in',
												'ultimate-3d-testimonial-slider'
											) }
										</h3>
										<Usage id={ id } />
									</>
								) : (
									<Button
										variant="link"
										onClick={ () => setShowUsage( true ) }
									>
										{ __(
											'Where is this used?',
											'ultimate-3d-testimonial-slider'
										) }
									</Button>
								) }
							</div>
						</Card>
					) }
				</div>
			</div>
		</div>
	);
};

export default Edit;
