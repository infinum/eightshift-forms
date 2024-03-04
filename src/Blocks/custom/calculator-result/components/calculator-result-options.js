import React from 'react';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { checkAttr,
	getAttrKey,
	AsyncSelect,
	STORE_NAME,
} from '@eightshift/frontend-libs/scripts';
import { outputFormSelectItemWithIcon } from '../../../components/utils';

export const CalculatorResultOptions = ({
	attributes,
	setAttributes,
	formSelectOptions,
	calculatorSelectOptions,
}) => {
	const manifest = select(STORE_NAME).getBlock('calculator-result');

	const calculatorResultFormPostId = checkAttr('calculatorResultFormPostId', attributes, manifest);
	const calculatorResultFormPostIdRaw = checkAttr('calculatorResultFormPostIdRaw', attributes, manifest);
	const calculatorResultPostId = checkAttr('calculatorResultPostId', attributes, manifest);
	const calculatorResultPostIdRaw = checkAttr('calculatorResultPostIdRaw', attributes, manifest);

	return (
		<PanelBody title={__('Calculator Result', 'infobip')}>
			<AsyncSelect
				label={__('Calculator Result', 'eightshift-forms')}
				help={__('If you can\'t find you output item, start typing its name while the dropdown is open.', 'eightshift-forms')}
				value={outputFormSelectItemWithIcon(Object.keys(calculatorResultPostIdRaw ?? {}).length ? calculatorResultPostIdRaw : {id: calculatorResultPostId})}
				loadOptions={calculatorSelectOptions}
				onChange={(value) => {
					setAttributes({
						[getAttrKey('calculatorResultPostIdRaw', attributes, manifest)]: {
							id: value?.id,
							label: value?.metadata?.label,
							value: value?.metadata?.value,
							metadata: value?.metadata?.metadata,
						},
						[getAttrKey('calculatorResultPostId', attributes, manifest)]: `${value?.value.toString()}`,
					});
				}}
			/>

			<AsyncSelect
				label={__('Form', 'eightshift-forms')}
				help={__('If you can\'t find you output item, start typing its name while the dropdown is open.', 'eightshift-forms')}
				value={outputFormSelectItemWithIcon(Object.keys(calculatorResultFormPostIdRaw ?? {}).length ? calculatorResultFormPostIdRaw : {id: calculatorResultFormPostId})}
				loadOptions={formSelectOptions}
				onChange={(value) => {
					setAttributes({
						[getAttrKey('calculatorResultFormPostIdRaw', attributes, manifest)]: {
							id: value?.id,
							label: value?.metadata?.label,
							value: value?.metadata?.value,
							metadata: value?.metadata?.metadata,
						},
						[getAttrKey('calculatorResultFormPostId', attributes, manifest)]: `${value?.value.toString()}`,
					});
				}}
			/>
		</PanelBody>
	);
};
