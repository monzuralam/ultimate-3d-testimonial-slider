/**
 * Settings page app: loads, edits and saves the uts_settings option
 * through the core /wp/v2/settings endpoint.
 */
import apiFetch from '@wordpress/api-fetch';
import {
	Button,
	Card,
	CardFooter,
	Notice,
	Spinner,
	TabPanel,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import Field from './components/Field';
import { TABS } from './fields';

const { schema, defaults } = window.utsSettingsData;

const isEqual = ( a, b ) => JSON.stringify( a ) === JSON.stringify( b );

const App = () => {
	const [ settings, setSettings ] = useState( null );
	const [ saved, setSaved ] = useState( null );
	const [ isSaving, setIsSaving ] = useState( false );
	const [ notice, setNotice ] = useState( null );

	const isDirty = !! settings && ! isEqual( settings, saved );

	useEffect( () => {
		apiFetch( { path: '/wp/v2/settings' } )
			.then( ( response ) => {
				const loaded = { ...defaults, ...response.uts_settings };
				setSettings( loaded );
				setSaved( loaded );
			} )
			.catch( ( error ) =>
				setNotice( { status: 'error', message: error.message } )
			);
	}, [] );

	// Warn before leaving with unsaved changes.
	useEffect( () => {
		if ( ! isDirty ) {
			return;
		}

		const onBeforeUnload = ( event ) => {
			event.preventDefault();
			event.returnValue = '';
		};

		window.addEventListener( 'beforeunload', onBeforeUnload );

		return () =>
			window.removeEventListener( 'beforeunload', onBeforeUnload );
	}, [ isDirty ] );

	const update = ( key, value ) =>
		setSettings( ( current ) => ( { ...current, [ key ]: value } ) );

	const save = () => {
		setIsSaving( true );
		setNotice( null );

		apiFetch( {
			path: '/wp/v2/settings',
			method: 'POST',
			data: { uts_settings: settings },
		} )
			.then( ( response ) => {
				setSettings( response.uts_settings );
				setSaved( response.uts_settings );
				setNotice( {
					status: 'success',
					message: __( 'Settings saved.', 'uts' ),
				} );
			} )
			.catch( ( error ) =>
				setNotice( { status: 'error', message: error.message } )
			)
			.finally( () => setIsSaving( false ) );
	};

	const renderTab = ( tab ) => (
		<div className="uts-settings__fields">
			{ tab.fields
				.filter(
					( field ) => ! field.showIf || field.showIf( settings )
				)
				.map( ( field ) => (
					<Field
						key={ field.key }
						field={ field }
						schema={ schema[ field.key ] }
						value={ settings[ field.key ] }
						onChange={ ( value ) => update( field.key, value ) }
					/>
				) ) }
		</div>
	);

	return (
		<div className="uts-settings">
			<h1>{ __( 'Testimonial Settings', 'uts' ) }</h1>
			<p className="uts-settings__intro">
				{ __(
					'Defaults for every [uts] shortcode. Shortcode attributes such as type="grid" or count="3" override these.',
					'uts'
				) }
			</p>

			{ notice && (
				<Notice
					status={ notice.status }
					onRemove={ () => setNotice( null ) }
				>
					{ notice.message }
				</Notice>
			) }

			{ settings && (
				<Card>
					<TabPanel className="uts-settings__tabs" tabs={ TABS }>
						{ renderTab }
					</TabPanel>
					<CardFooter className="uts-settings__footer">
						<Button
							variant="tertiary"
							isDestructive
							disabled={
								isSaving || isEqual( settings, defaults )
							}
							onClick={ () => setSettings( { ...defaults } ) }
						>
							{ __( 'Reset to defaults', 'uts' ) }
						</Button>
						<Button
							variant="primary"
							__next40pxDefaultSize
							isBusy={ isSaving }
							disabled={ isSaving || ! isDirty }
							onClick={ save }
						>
							{ __( 'Save changes', 'uts' ) }
						</Button>
					</CardFooter>
				</Card>
			) }

			{ ! settings && ! notice && <Spinner /> }
		</div>
	);
};

export default App;
