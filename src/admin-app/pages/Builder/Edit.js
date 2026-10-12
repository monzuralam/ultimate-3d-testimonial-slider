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
		return __( 'On', 'uts' );
	}
	if ( false === value ) {
		return __( 'Off', 'uts' );
	}
	if ( '' === value || null === value || undefined === value ) {
		return __( 'Theme default', 'uts' );
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
	const [ item, setItem ] = useState(
		id ? null : { ...EMPTY, settings: {} }
	);
	const [ saved, setSaved ] = useState( id ? null : snapshot( EMPTY ) );
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
		return notice ? (
			<>
				<Notice status="error" isDismissible={ false }>
					{ notice.message }
				</Notice>
				<p>
					<Button
						variant="link"
						onClick={ () => navigate( { page: 'uts-builder' } ) }
					>
						{ __( '← Back to all sliders', 'uts' ) }
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
		if ( '' === item.title.trim() ) {
			setNotice( {
				status: 'error',
				message: __( 'Please give the slider a name.', 'uts' ),
			} );
			return;
		}

		setIsSaving( true );
		setNotice( null );

		const payload = {
			title: item.title.trim(),
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
									'uts'
								)
							: __(
									'Draft saved. Publish it to use its shortcode on your site.',
									'uts'
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
									label: __( 'Layout', 'uts' ),
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
							{ __( 'Changed', 'uts' ) }
						</span>
						<Button
							variant="link"
							className="uts:text-[12px]!"
							onClick={ () => resetOption( field.key ) }
						>
							{ sprintf(
								/* translators: %s: global default value */ __(
									'Reset to default (%s)',
									'uts'
								),
								display( data.settings[ field.key ] )
							) }
						</Button>
					</p>
				) }
			</div>
		);
	};

	const renderTab = ( tab ) => (
		<div className="uts-settings__fields">
			{ 'general' === tab.name && (
				<SelectControl
					label={ __( 'Category', 'uts' ) }
					help={ __(
						'Show only testimonials from one category.',
						'uts'
					) }
					value={ changed.category || '' }
					options={ [
						{ value: '', label: __( 'All categories', 'uts' ) },
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
			{ tab.fields
				.filter(
					( field ) => ! field.showIf || field.showIf( effective )
				)
				.map( renderField ) }
		</div>
	);

	const changedCount = Object.keys( changed ).length;

	return (
		<div className="uts-builder-edit">
			<p className="uts:mt-0 uts:mb-2">
				<a
					href={ routeUrl( { page: 'uts-builder' } ) }
					onClick={ back }
				>
					{ __( '← All sliders', 'uts' ) }
				</a>
			</p>
			<h1 className="uts:mt-0 uts:mb-4">
				{ id ? __( 'Edit Slider', 'uts' ) : __( 'New Slider', 'uts' ) }
			</h1>

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
					<Card>
						<TextControl
							label={ __( 'Name', 'uts' ) }
							help={ __(
								'Only shown here, to tell your sliders apart.',
								'uts'
							) }
							placeholder={ __( 'e.g. Homepage slider', 'uts' ) }
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
					</Card>

					<WPCard>
						<CardBody className="uts:pb-0!">
							<p className="uts:m-0 uts:text-wp-muted">
								{ changedCount
									? sprintf(
											/* translators: %s: number of options */ __(
												'This slider changes %s option(s). All others follow your Settings.',
												'uts'
											),
											changedCount
										)
									: __(
											'This slider uses your Settings. Change any option below to make it different.',
											'uts'
										) }
							</p>
						</CardBody>
						<TabPanel className="uts-settings__tabs" tabs={ TABS }>
							{ renderTab }
						</TabPanel>
					</WPCard>

					<Card>
						<div className="uts:flex uts:flex-wrap uts:items-center uts:justify-between uts:gap-3">
							<span className="uts:text-wp-muted">
								{ id
									? sprintf(
											/* translators: %s: Published or Draft */ __(
												'Status: %s',
												'uts'
											),
											'publish' === item.status
												? __( 'Published', 'uts' )
												: __( 'Draft', 'uts' )
										)
									: __( 'Not saved yet', 'uts' ) }
							</span>
							<div className="uts:flex uts:gap-2">
								<Button
									variant="secondary"
									disabled={ isSaving }
									onClick={ () => save( 'draft' ) }
								>
									{ 'publish' === item.status && id
										? __( 'Switch to Draft', 'uts' )
										: __( 'Save Draft', 'uts' ) }
								</Button>
								<Button
									variant="primary"
									isBusy={ isSaving }
									disabled={
										isSaving ||
										( !! id &&
											! isDirty &&
											'publish' === item.status )
									}
									onClick={ () => save( 'publish' ) }
								>
									{ id && 'publish' === item.status
										? __( 'Update', 'uts' )
										: __( 'Publish', 'uts' ) }
								</Button>
							</div>
						</div>
					</Card>
				</div>

				<div className="uts:flex uts:min-w-0 uts:flex-col uts:gap-6 uts:xl:sticky uts:xl:top-12 uts:xl:self-start">
					<Card title={ __( 'Live preview', 'uts' ) }>
						<Preview attributes={ { ...changed } } />
					</Card>

					{ id > 0 && (
						<Card title={ __( 'Shortcode', 'uts' ) }>
							<CopyField
								value={ item.shortcode }
								className="uts:max-w-md"
							/>
							<p className="uts:mb-0 uts:text-wp-muted">
								{ 'publish' === item.status
									? __(
											'Paste it into any page. Attributes still override the slider, e.g. add autoplay="false".',
											'uts'
										)
									: __(
											'Drafts show nothing on your site. Publish the slider to use this shortcode.',
											'uts'
										) }
							</p>
							<div className="uts:mt-4">
								{ showUsage ? (
									<>
										<h3 className="uts:m-0 uts:mb-2 uts:text-sm">
											{ __( 'Used in', 'uts' ) }
										</h3>
										<Usage id={ id } />
									</>
								) : (
									<Button
										variant="link"
										onClick={ () => setShowUsage( true ) }
									>
										{ __( 'Where is this used?', 'uts' ) }
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
