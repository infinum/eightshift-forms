import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl } from '@wordpress/components';

export const LabelOptions = (props) => {
	const {
		label,
		onChangeLabel,
	} = props;

	return (
		<PanelBody title={__('Label Settings', 'eightshift-forms')}>
			{onChangeLabel &&
				<TextControl
					label={__('Label', 'eightshift-forms')}
					value={label}
					onChange={onChangeLabel}
				/>
			}
		</PanelBody>
	);
};

