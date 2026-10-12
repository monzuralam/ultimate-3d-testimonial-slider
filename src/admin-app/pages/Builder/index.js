/**
 * Shortcode Builder: list of saved sliders, or the editor (new / edit).
 */
import Edit from './Edit';
import List from './List';

/**
 * @param {Object} props
 * @param {Object} props.route Current route ({ view, id }).
 */
const Builder = ( { route } ) =>
	'new' === route.view || 'edit' === route.view ? (
		<Edit
			key={ route.view + route.id }
			id={ 'edit' === route.view ? route.id : 0 }
		/>
	) : (
		<List />
	);

export default Builder;
