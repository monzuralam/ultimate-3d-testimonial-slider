/**
 * Shortcode Builder list: saved sliders with search, status views,
 * pagination and row actions (edit, duplicate, trash, restore, delete).
 */
import {
	Button,
	Modal,
	Notice,
	Spinner,
	TextControl,
} from '@wordpress/components';
import { dateI18n, getSettings as getDateSettings } from '@wordpress/date';
import { useCallback, useEffect, useState } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';

import {
	deleteShortcode,
	duplicateShortcode,
	getStatus,
	listShortcodes,
	trashShortcode,
	updateShortcode,
} from '../../api';
import { useApp } from '../../App';
import CopyField from '../../components/CopyField';
import data from '../../data';
import { isModifiedClick, routeUrl } from '../../router';
import Usage from './Usage';

const PER_PAGE = 20;

const formatDate = ( rfc3339 ) => {
	const { formats } = getDateSettings();
	// The API returns GMT without a zone; mark it as UTC so it is shown in site time.
	return dateI18n( `${ formats.date } ${ formats.time }`, `${ rfc3339 }Z` );
};

const STATUS_LABELS = {
	publish: __( 'Published', 'ultimate-3d-testimonial-slider' ),
	draft: __( 'Draft', 'ultimate-3d-testimonial-slider' ),
	trash: __( 'Trash', 'ultimate-3d-testimonial-slider' ),
};

const List = () => {
	const { navigate } = useApp();
	const [ view, setView ] = useState( 'any' );
	const [ search, setSearch ] = useState( '' );
	const [ query, setQuery ] = useState( '' );
	const [ page, setPage ] = useState( 1 );
	const [ result, setResult ] = useState( null );
	const [ counts, setCounts ] = useState(
		data.status.shortcodes || { publish: 0, draft: 0, trash: 0 }
	);
	const [ notice, setNotice ] = useState( null );
	const [ busy, setBusy ] = useState( 0 );
	const [ confirm, setConfirm ] = useState( null );

	const load = useCallback( () => {
		setResult( null );
		listShortcodes( {
			status: view,
			search: query,
			page,
			per_page: PER_PAGE,
		} )
			.then( setResult )
			.catch( ( error ) => {
				setResult( { items: [], total: 0, totalPages: 0 } );
				setNotice( { status: 'error', message: error.message } );
			} );
	}, [ view, query, page ] );

	useEffect( load, [ load ] );

	// The counts in the boot data are from page load; refresh them when the list opens.
	useEffect( () => {
		getStatus()
			.then( ( status ) => {
				data.status = status;
				setCounts( status.shortcodes );
			} )
			.catch( () => {} );
	}, [] );

	// Debounce the search box.
	useEffect( () => {
		const timer = setTimeout( () => {
			setPage( 1 );
			setQuery( search.trim() );
		}, 300 );
		return () => clearTimeout( timer );
	}, [ search ] );

	const refresh = ( message ) => {
		if ( message ) {
			setNotice( { status: 'success', message } );
		}
		load();
		getStatus()
			.then( ( status ) => {
				data.status = status;
				setCounts( status.shortcodes );
			} )
			.catch( () => {} );
	};

	const run = ( id, action, message ) => {
		setBusy( id );
		setNotice( null );
		return action()
			.then( () => refresh( message ) )
			.catch( ( error ) =>
				setNotice( { status: 'error', message: error.message } )
			)
			.finally( () => setBusy( 0 ) );
	};

	const open = ( event, route ) => {
		if ( isModifiedClick( event ) ) {
			return;
		}
		event.preventDefault();
		navigate( route );
	};

	const allCount = counts.publish + counts.draft;
	const views = [
		[ 'any', __( 'All', 'ultimate-3d-testimonial-slider' ), allCount ],
		[
			'publish',
			__( 'Published', 'ultimate-3d-testimonial-slider' ),
			counts.publish,
		],
		[
			'draft',
			__( 'Drafts', 'ultimate-3d-testimonial-slider' ),
			counts.draft,
		],
		[
			'trash',
			__( 'Trash', 'ultimate-3d-testimonial-slider' ),
			counts.trash,
		],
	];

	const rowActions = ( item ) => {
		if ( 'trash' === item.status ) {
			return [
				<Button
					key="restore"
					variant="link"
					onClick={ () =>
						run(
							item.id,
							() =>
								updateShortcode( item.id, { status: 'draft' } ),
							__(
								'Slider restored as a draft.',
								'ultimate-3d-testimonial-slider'
							)
						)
					}
				>
					{ __( 'Restore', 'ultimate-3d-testimonial-slider' ) }
				</Button>,
				<Button
					key="delete"
					variant="link"
					isDestructive
					onClick={ () => setConfirm( { item, force: true } ) }
				>
					{ __(
						'Delete permanently',
						'ultimate-3d-testimonial-slider'
					) }
				</Button>,
			];
		}
		return [
			<a
				key="edit"
				href={ routeUrl( {
					page: 'uts-builder',
					view: 'edit',
					id: item.id,
				} ) }
				onClick={ ( event ) =>
					open( event, {
						page: 'uts-builder',
						view: 'edit',
						id: item.id,
					} )
				}
			>
				{ __( 'Edit', 'ultimate-3d-testimonial-slider' ) }
			</a>,
			<Button
				key="dup"
				variant="link"
				onClick={ () =>
					run(
						item.id,
						() => duplicateShortcode( item.id ),
						__(
							'Slider duplicated as a draft.',
							'ultimate-3d-testimonial-slider'
						)
					)
				}
			>
				{ __( 'Duplicate', 'ultimate-3d-testimonial-slider' ) }
			</Button>,
			<Button
				key="trash"
				variant="link"
				isDestructive
				onClick={ () => setConfirm( { item, force: false } ) }
			>
				{ __( 'Trash', 'ultimate-3d-testimonial-slider' ) }
			</Button>,
		];
	};

	return (
		<div className="uts-builder">
			<div className="uts:mb-4 uts:flex uts:flex-wrap uts:items-center uts:gap-3">
				<h1 className="wp-heading-inline uts:m-0">
					{ __(
						'Shortcode Builder',
						'ultimate-3d-testimonial-slider'
					) }
				</h1>
				<a
					href={ routeUrl( { page: 'uts-builder', view: 'new' } ) }
					className="page-title-action"
					onClick={ ( event ) =>
						open( event, { page: 'uts-builder', view: 'new' } )
					}
				>
					{ __( 'Add New Slider', 'ultimate-3d-testimonial-slider' ) }
				</a>
			</div>
			<p className="uts:mt-0 uts:mb-4 uts:max-w-3xl uts:text-wp-muted">
				{ __(
					'Save named sliders with their own look, then paste their shortcode anywhere. Editing a slider updates every page that uses it. Options a slider does not change follow your Settings.',
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

			<div className="uts:mb-3 uts:flex uts:flex-wrap uts:items-end uts:justify-between uts:gap-3">
				<ul className="subsubsub uts:m-0 uts:float-none!">
					{ views.map( ( [ key, label, count ], index ) => (
						<li key={ key }>
							<a
								href="#uts-builder-list"
								className={ view === key ? 'current' : '' }
								aria-current={
									view === key ? 'page' : undefined
								}
								onClick={ ( event ) => {
									event.preventDefault();
									setPage( 1 );
									setView( key );
								} }
							>
								{ label }{ ' ' }
								<span className="count">({ count })</span>
							</a>
							{ index < views.length - 1 ? ' |' : '' }
						</li>
					) ) }
				</ul>
				<div className="uts:w-64">
					<TextControl
						type="search"
						label={ __(
							'Search sliders',
							'ultimate-3d-testimonial-slider'
						) }
						hideLabelFromVision
						placeholder={ __(
							'Search sliders…',
							'ultimate-3d-testimonial-slider'
						) }
						value={ search }
						onChange={ setSearch }
						__nextHasNoMarginBottom
					/>
				</div>
			</div>

			{ ! result && <Spinner /> }

			{ result && 0 === result.total && ! query && 'any' === view && (
				<div className="uts:flex uts:flex-col uts:items-center uts:rounded-lg uts:border uts:border-dashed uts:border-wp-border uts:bg-white uts:px-6 uts:py-14 uts:text-center">
					<span
						className="dashicons dashicons-slides uts:size-12 uts:text-5xl uts:text-wp-border"
						aria-hidden="true"
					/>
					<p className="uts:mt-3 uts:mb-1 uts:text-lg uts:font-semibold uts:text-gray-900">
						{ __(
							'No saved sliders yet',
							'ultimate-3d-testimonial-slider'
						) }
					</p>
					<p className="uts:mt-0 uts:mb-5 uts:max-w-md uts:text-wp-muted">
						{ __(
							'Create a slider for each place it appears, for example "Homepage" or "Pricing page", each with its own layout and effect.',
							'ultimate-3d-testimonial-slider'
						) }
					</p>
					<Button
						variant="primary"
						onClick={ () =>
							navigate( { page: 'uts-builder', view: 'new' } )
						}
					>
						{ __(
							'Create your first slider',
							'ultimate-3d-testimonial-slider'
						) }
					</Button>
				</div>
			) }

			{ result && ( result.total > 0 || query || 'any' !== view ) && (
				<table
					id="uts-builder-list"
					className="wp-list-table widefat fixed striped"
				>
					<thead>
						<tr>
							<th scope="col" className="uts:w-[34%]">
								{ __(
									'Title',
									'ultimate-3d-testimonial-slider'
								) }
							</th>
							<th scope="col" className="uts:w-[30%]">
								{ __(
									'Shortcode',
									'ultimate-3d-testimonial-slider'
								) }
							</th>
							<th scope="col">
								{ __(
									'Status',
									'ultimate-3d-testimonial-slider'
								) }
							</th>
							<th scope="col">
								{ __(
									'Last modified',
									'ultimate-3d-testimonial-slider'
								) }
							</th>
						</tr>
					</thead>
					<tbody>
						{ 0 === result.items.length && (
							<tr>
								<td colSpan="4">
									{ query
										? __(
												'No sliders match your search.',
												'ultimate-3d-testimonial-slider'
											)
										: __(
												'No sliders found.',
												'ultimate-3d-testimonial-slider'
											) }
								</td>
							</tr>
						) }
						{ result.items.map( ( item ) => (
							<tr
								key={ item.id }
								className={
									busy === item.id ? 'uts:opacity-50' : ''
								}
							>
								<td>
									<strong>
										{ 'trash' === item.status ? (
											item.title
										) : (
											<a
												className="row-title"
												href={ routeUrl( {
													page: 'uts-builder',
													view: 'edit',
													id: item.id,
												} ) }
												onClick={ ( event ) =>
													open( event, {
														page: 'uts-builder',
														view: 'edit',
														id: item.id,
													} )
												}
											>
												{ item.title }
											</a>
										) }
										{ 'draft' === item.status &&
										view !== 'draft'
											? ` — ${ STATUS_LABELS.draft }`
											: '' }
									</strong>
									<div className="uts:mt-1 uts:flex uts:flex-wrap uts:items-center uts:gap-x-3 uts:text-[13px]">
										{ rowActions( item ) }
									</div>
								</td>
								<td>
									<CopyField value={ item.shortcode } />
								</td>
								<td>{ STATUS_LABELS[ item.status ] }</td>
								<td>{ formatDate( item.updated_at ) }</td>
							</tr>
						) ) }
					</tbody>
				</table>
			) }

			{ result && result.totalPages > 1 && (
				<div className="uts:mt-3 uts:flex uts:items-center uts:justify-end uts:gap-2">
					<span className="uts:text-wp-muted">
						{ sprintf(
							/* translators: %s: number of sliders */ _n(
								'%s item',
								'%s items',
								result.total,
								'ultimate-3d-testimonial-slider'
							),
							result.total
						) }
					</span>
					<Button
						variant="secondary"
						disabled={ page <= 1 }
						onClick={ () => setPage( page - 1 ) }
						label={ __(
							'Previous page',
							'ultimate-3d-testimonial-slider'
						) }
					>
						‹
					</Button>
					<span>
						{ sprintf(
							/* translators: 1: current page, 2: total pages */ __(
								'%1$s of %2$s',
								'ultimate-3d-testimonial-slider'
							),
							page,
							result.totalPages
						) }
					</span>
					<Button
						variant="secondary"
						disabled={ page >= result.totalPages }
						onClick={ () => setPage( page + 1 ) }
						label={ __(
							'Next page',
							'ultimate-3d-testimonial-slider'
						) }
					>
						›
					</Button>
				</div>
			) }

			{ confirm && (
				<Modal
					title={
						confirm.force
							? __(
									'Delete this slider permanently?',
									'ultimate-3d-testimonial-slider'
								)
							: __(
									'Move this slider to the trash?',
									'ultimate-3d-testimonial-slider'
								)
					}
					onRequestClose={ () => setConfirm( null ) }
				>
					<p className="uts:mt-0">
						<strong>{ confirm.item.title }</strong>{ ' ' }
						<code>{ confirm.item.shortcode }</code>
					</p>
					<p>
						{ __(
							'Pages that use its shortcode will show nothing in its place. Used in:',
							'ultimate-3d-testimonial-slider'
						) }
					</p>
					<Usage id={ confirm.item.id } />
					<div className="uts:mt-6 uts:flex uts:justify-end uts:gap-3">
						<Button
							variant="tertiary"
							onClick={ () => setConfirm( null ) }
						>
							{ __( 'Cancel', 'ultimate-3d-testimonial-slider' ) }
						</Button>
						<Button
							variant="primary"
							isDestructive
							onClick={ () => {
								const { item, force } = confirm;
								setConfirm( null );
								run(
									item.id,
									() =>
										force
											? deleteShortcode( item.id )
											: trashShortcode( item.id ),
									force
										? __(
												'Slider deleted permanently.',
												'ultimate-3d-testimonial-slider'
											)
										: __(
												'Slider moved to the trash.',
												'ultimate-3d-testimonial-slider'
											)
								);
							} }
						>
							{ confirm.force
								? __(
										'Delete permanently',
										'ultimate-3d-testimonial-slider'
									)
								: __(
										'Move to Trash',
										'ultimate-3d-testimonial-slider'
									) }
						</Button>
					</div>
				</Modal>
			) }
		</div>
	);
};

export default List;
