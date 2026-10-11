/**
 * Renders one setting with the control matching its type.
 */
import {
	BaseControl,
	ColorPalette,
	RangeControl,
	SelectControl,
	ToggleControl,
} from '@wordpress/components';
import { useInstanceId } from '@wordpress/compose';

const Field = ( { field, schema, value, onChange } ) => {
	const id = useInstanceId( Field, 'uts-field' );
	const common = {
		label: field.label,
		help: field.help,
		__nextHasNoMarginBottom: true,
	};

	switch ( field.control ) {
		case 'toggle':
			return (
				<ToggleControl
					{ ...common }
					checked={ !! value }
					onChange={ onChange }
				/>
			);

		case 'range':
			return (
				<RangeControl
					{ ...common }
					__next40pxDefaultSize
					value={ value }
					min={ schema.minimum }
					max={ schema.maximum }
					step={ field.step || 1 }
					allowReset
					resetFallbackValue={ schema.default }
					onChange={ ( next ) =>
						onChange( undefined === next ? schema.default : next )
					}
				/>
			);

		case 'select':
			return (
				<SelectControl
					{ ...common }
					__next40pxDefaultSize
					value={ value }
					options={ schema.enum.map( ( option ) => ( {
						value: option,
						label: field.options[ option ] || option,
					} ) ) }
					onChange={ onChange }
				/>
			);

		case 'color':
			return (
				<BaseControl { ...common } id={ id }>
					<ColorPalette
						value={ value || undefined }
						onChange={ ( next ) => onChange( next || '' ) }
						enableAlpha={ false }
						clearable
					/>
				</BaseControl>
			);

		default:
			return null;
	}
};

export default Field;
