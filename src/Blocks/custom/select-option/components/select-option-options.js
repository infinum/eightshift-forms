import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';

export const SelectOptionOptions = (props) => {
	const {
		attributes: {
			label,
			value,
			isSelected,
			isDisabled,
		},
		actions: {
			onChangeLabel,
			onChangeValue,
			onChangeIsSelected,
			onChangeIsDisabled,
		},
	} = props;

	return (
		<PanelBody title={__('Select Option Settings', 'eightshift-forms')}>
			{onChangeLabel &&
				<TextControl
					label={__('Label', 'eightshift-forms')}
					value={label}
					onChange={onChangeLabel}
				/>
			}

			{onChangeValue &&
				<TextControl
					label={__('Value', 'eightshift-forms')}
					value={value}
					onChange={onChangeValue}
				/>
			}

			{onChangeIsSelected &&
				<ToggleControl
					label={__('Selected', 'eightshift-forms')}
					checked={isSelected}
					onChange={onChangeIsSelected}
				/>
			}

			{onChangeIsDisabled &&
				<ToggleControl
					label={__('Disabled', 'eightshift-forms')}
					checked={isDisabled}
					onChange={onChangeIsDisabled}
				/>
			}

		</PanelBody>
	);
};
