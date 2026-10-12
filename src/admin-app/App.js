/**
 * Admin app: shared header plus the current page, with in-app navigation
 * (no reloads) and an unsaved-changes guard.
 */
import { Button, Modal } from '@wordpress/components';
import {
	createContext,
	useCallback,
	useContext,
	useEffect,
	useRef,
	useState,
} from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import Header from './components/Header';
import data from './data';
import Builder from './pages/Builder';
import GettingStarted from './pages/GettingStarted';
import Settings from './pages/Settings';
import { applyRoute, parseRoute } from './router';

const AppContext = createContext( {
	navigate: () => {},
	setDirty: () => {},
} );

/**
 * Access navigate( route, { replace } ) and setDirty( bool ) from pages.
 *
 * @return {{navigate: Function, setDirty: Function}} App helpers.
 */
export const useApp = () => useContext( AppContext );

const PAGES = {
	'uts-getting-started': GettingStarted,
	'uts-builder': Builder,
	'uts-settings': Settings,
};

const App = () => {
	const [ route, setRoute ] = useState( () =>
		parseRoute( window.location.search )
	);
	const [ pending, setPending ] = useState( null );
	const dirty = useRef( false );
	const current = useRef( route );
	current.current = route;

	const setDirty = useCallback( ( value ) => {
		dirty.current = !! value;
	}, [] );

	const go = useCallback( ( next, replace ) => {
		dirty.current = false;
		applyRoute( next, replace );
		setRoute( next );
		window.scrollTo( 0, 0 );
	}, [] );

	const navigate = useCallback(
		( next, options = {} ) => {
			const target = { view: 'list', id: 0, ...next };
			if ( dirty.current && ! options.force ) {
				setPending( { route: target, replace: !! options.replace } );
				return;
			}
			go( target, !! options.replace );
		},
		[ go ]
	);

	// Canonical URL for the first screen (keeps history.state in sync).
	useEffect( () => {
		applyRoute( route, true );
	}, [] ); // eslint-disable-line react-hooks/exhaustive-deps

	// Browser back/forward.
	useEffect( () => {
		const onPopState = () => {
			const next = parseRoute( window.location.search );
			if ( dirty.current ) {
				// The URL already changed: put the current screen back until the user confirms.
				applyRoute( current.current );
				setPending( { route: next, replace: false } );
				return;
			}
			go( next, true );
		};
		window.addEventListener( 'popstate', onPopState );
		return () => window.removeEventListener( 'popstate', onPopState );
	}, [ go ] );

	// Leaving the admin page with unsaved changes.
	useEffect( () => {
		const onBeforeUnload = ( event ) => {
			if ( dirty.current ) {
				event.preventDefault();
				event.returnValue = '';
			}
		};
		window.addEventListener( 'beforeunload', onBeforeUnload );
		return () =>
			window.removeEventListener( 'beforeunload', onBeforeUnload );
	}, [] );

	const Page = data.pages[ route.page ] ? PAGES[ route.page ] : null;

	return (
		<AppContext.Provider value={ { navigate, setDirty } }>
			<Header page={ route.page } navigate={ navigate } />
			<div className="wrap uts-app uts:mt-6 uts:max-w-[1280px]">
				{ Page ? (
					<Page route={ route } />
				) : (
					<div className="notice notice-error">
						<p>
							{ __(
								'Sorry, you are not allowed to access this page.',
								'ultimate-3d-testimonial-slider'
							) }
						</p>
					</div>
				) }
			</div>

			{ pending && (
				<Modal
					title={ __(
						'Leave without saving?',
						'ultimate-3d-testimonial-slider'
					) }
					onRequestClose={ () => setPending( null ) }
				>
					<p>
						{ __(
							'You have unsaved changes on this page. If you leave now, they will be lost.',
							'ultimate-3d-testimonial-slider'
						) }
					</p>
					<div className="uts:mt-6 uts:flex uts:justify-end uts:gap-3">
						<Button
							variant="tertiary"
							onClick={ () => setPending( null ) }
						>
							{ __(
								'Stay on this page',
								'ultimate-3d-testimonial-slider'
							) }
						</Button>
						<Button
							variant="primary"
							isDestructive
							onClick={ () => {
								const { route: next, replace } = pending;
								setPending( null );
								go( next, replace );
							} }
						>
							{ __(
								'Leave and discard changes',
								'ultimate-3d-testimonial-slider'
							) }
						</Button>
					</div>
				</Modal>
			) }
		</AppContext.Provider>
	);
};

export default App;
