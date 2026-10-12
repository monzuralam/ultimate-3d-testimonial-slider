/**
 * "Where is this used?": posts whose content uses [uts id="…"].
 */
import { Spinner } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { shortcodeUsage } from '../../api';

/**
 * @param {Object}   props
 * @param {number}   props.id       Saved shortcode ID.
 * @param {Function} props.onLoaded Called with the posts once loaded.
 */
const Usage = ( { id, onLoaded } ) => {
	const [ posts, setPosts ] = useState( null );
	const [ error, setError ] = useState( '' );

	useEffect( () => {
		let active = true;
		shortcodeUsage( id )
			.then( ( result ) => {
				if ( active ) {
					setPosts( result );
					if ( onLoaded ) {
						onLoaded( result );
					}
				}
			} )
			.catch( ( err ) => active && setError( err.message ) );
		return () => {
			active = false;
		};
	}, [ id ] ); // eslint-disable-line react-hooks/exhaustive-deps

	if ( error ) {
		return <p className="uts:m-0 uts:text-red-700">{ error }</p>;
	}
	if ( ! posts ) {
		return <Spinner />;
	}
	if ( ! posts.length ) {
		return (
			<p className="uts:m-0 uts:text-wp-muted">
				{ __( 'Not used in any post or page yet.', 'uts' ) }
			</p>
		);
	}

	return (
		<ul className="uts:m-0 uts:list-none uts:space-y-1 uts:p-0">
			{ posts.map( ( post ) => (
				<li key={ post.id } className="uts:m-0">
					<a href={ post.edit_link || post.link }>{ post.title }</a>{ ' ' }
					<span className="uts:text-wp-muted">
						({ post.type }
						{ 'publish' !== post.status
							? `, ${ post.status }`
							: '' }
						)
					</span>
				</li>
			) ) }
		</ul>
	);
};

export default Usage;
