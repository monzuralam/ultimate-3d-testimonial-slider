/**
 * Settings page: loads, edits and saves the uts_settings option through
 * the core /wp/v2/settings endpoint.
 */
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

import { getSettings, saveSettings } from '../api';
import { useApp } from '../App';
import Field from '../components/Field';
import data from '../data';
import { TABS } from '../fields';

const isEqual = ( a, b ) => JSON.stringify( a ) === JSON.stringify( b );

const Settings = () => {
	const { setDirty } = useApp();
	const [ settings, setSettings ] = useState( null );
	const [ saved, setSaved ] = useState( null );
	const [ isSaving, setIsSaving ] = useState( false );
	const [ notice, setNotice ] = useState( null );

	const isDirty = !! settings && ! isEqual( settings, saved );

	useEffect( () => {
		getSettings()
			.then( ( loaded ) => {
				const merged = { ...data.defaults, ...loaded };
				setSettings( merged );
				setSaved( merged );
			} )
			.catch( ( error ) =>
				setNotice( { status: 'error', message: error.message } )
			);
	}, [] );

	useEffect( () => {
		setDirty( isDirty );
	}, [ isDirty, setDirty ] );

	const update = ( key, value ) =>
		setSettings( ( current ) => ( { ...current, [ key ]: value } ) );

	const save = () => {
		setIsSaving( true );
		setNotice( null );

		saveSettings( settings )
			.then( ( stored ) => {
				setSettings( stored );
				setSaved( stored );
				// Other screens (Builder, Getting Started) read the globals from here.
				data.settings = stored;
				data.status.settings_saved = true;
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
						schema={ data.schema[ field.key ] }
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
					'Defaults for every [uts] shortcode. Saved sliders from the Shortcode Builder and shortcode attributes such as type="grid" or count="3" override these.',
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
								isSaving || isEqual( settings, data.defaults )
							}
							onClick={ () =>
								setSettings( { ...data.defaults } )
							}
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

export default Settings;
