/**
 * Settings page: loads, edits and saves the uts_settings option through
 * the core /wp/v2/settings endpoint.
 */
import {
	Button,
	Card,
	CardFooter,
	Notice as InlineNotice,
	Notice,
	Spinner,
	TabPanel,
	ToggleControl,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { getSettings, saveSettings } from '../api';
import { useApp } from '../App';
import Field from '../components/Field';
import data from '../data';
import { TABS } from '../fields';

const isEqual = ( a, b ) => JSON.stringify( a ) === JSON.stringify( b );

// Settings-only tab; the Builder shares TABS and must not get it.
const TABS_WITH_ADVANCED = [
	...TABS,
	{
		name: 'advanced',
		title: __( 'Advanced', 'ultimate-3d-testimonial-slider' ),
		fields: [],
	},
];

const Settings = () => {
	const { setDirty } = useApp();
	const [ settings, setSettings ] = useState( null );
	const [ saved, setSaved ] = useState( null );
	const [ keepData, setKeepData ] = useState( true );
	const [ savedKeepData, setSavedKeepData ] = useState( true );
	const [ isSaving, setIsSaving ] = useState( false );
	const [ notice, setNotice ] = useState( null );

	const isDirty =
		!! settings &&
		( ! isEqual( settings, saved ) || keepData !== savedKeepData );

	useEffect( () => {
		getSettings()
			.then( ( loaded ) => {
				const merged = { ...data.defaults, ...loaded.settings };
				setSettings( merged );
				setSaved( merged );
				setKeepData( loaded.keepData );
				setSavedKeepData( loaded.keepData );
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

		saveSettings( settings, keepData )
			.then( ( stored ) => {
				setSettings( stored.settings );
				setSaved( stored.settings );
				setKeepData( stored.keepData );
				setSavedKeepData( stored.keepData );
				// Other screens (Builder, Getting Started) read the globals from here.
				data.settings = stored.settings;
				data.status.settings_saved = true;
				setNotice( {
					status: 'success',
					message: __(
						'Settings saved.',
						'ultimate-3d-testimonial-slider'
					),
				} );
			} )
			.catch( ( error ) =>
				setNotice( { status: 'error', message: error.message } )
			)
			.finally( () => setIsSaving( false ) );
	};

	const renderAdvanced = () => (
		<div className="uts-settings__fields">
			<ToggleControl
				label={ __(
					'Keep data when the plugin is deleted',
					'ultimate-3d-testimonial-slider'
				) }
				help={
					keepData
						? __(
								'Deleting the plugin keeps your settings and saved sliders, so reinstalling brings them back.',
								'ultimate-3d-testimonial-slider'
							)
						: __(
								'Deleting the plugin removes your settings and saved sliders permanently.',
								'ultimate-3d-testimonial-slider'
							)
				}
				checked={ keepData }
				onChange={ setKeepData }
				__nextHasNoMarginBottom
			/>
			{ ! keepData && (
				<InlineNotice status="warning" isDismissible={ false }>
					{ __(
						'When the plugin is deleted from the Plugins screen, its settings and saved sliders are removed and cannot be restored. Deactivating the plugin does not remove anything.',
						'ultimate-3d-testimonial-slider'
					) }
				</InlineNotice>
			) }
			<p className="uts:m-0 uts:text-wp-muted">
				{ __(
					'Your testimonials are never deleted: they are normal posts and stay either way.',
					'ultimate-3d-testimonial-slider'
				) }
			</p>
		</div>
	);

	const renderTab = ( tab ) =>
		'advanced' === tab.name ? (
			renderAdvanced()
		) : (
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
			<h1>
				{ __(
					'Testimonial Settings',
					'ultimate-3d-testimonial-slider'
				) }
			</h1>
			<p className="uts-settings__intro">
				{ __(
					'Defaults for every [uts] shortcode. Saved sliders from the Shortcode Builder and shortcode attributes such as type="grid" or count="3" override these.',
					'ultimate-3d-testimonial-slider'
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
					<TabPanel
						className="uts-settings__tabs"
						tabs={ TABS_WITH_ADVANCED }
					>
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
							{ __(
								'Reset to defaults',
								'ultimate-3d-testimonial-slider'
							) }
						</Button>
						<Button
							variant="primary"
							__next40pxDefaultSize
							isBusy={ isSaving }
							disabled={ isSaving || ! isDirty }
							onClick={ save }
						>
							{ __(
								'Save changes',
								'ultimate-3d-testimonial-slider'
							) }
						</Button>
					</CardFooter>
				</Card>
			) }

			{ ! settings && ! notice && <Spinner /> }
		</div>
	);
};

export default Settings;
