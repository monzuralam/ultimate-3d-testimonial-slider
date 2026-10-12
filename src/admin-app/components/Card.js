/**
 * White card used by every page.
 *
 * @param {Object}  props
 * @param {string}  props.title     Optional heading (h2).
 * @param {string}  props.className Extra classes.
 * @param {Element} props.children  Content.
 */
const Card = ( { title, className = '', children } ) => (
	<section
		className={ `uts:rounded-lg uts:border uts:border-wp-border uts:bg-white uts:p-5 uts:md:p-7 ${ className }` }
	>
		{ title && (
			<h2 className="uts:m-0 uts:mb-4 uts:text-lg uts:font-semibold uts:text-gray-900">
				{ title }
			</h2>
		) }
		{ children }
	</section>
);

export default Card;
