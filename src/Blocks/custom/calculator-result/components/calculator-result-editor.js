import React from 'react';
import { select } from '@wordpress/data';
import { ServerSideRender,
	checkAttr,
	icons,
	AsyncSelect,
	getAttrKey,
	STORE_NAME,
} from '@eightshift/frontend-libs/scripts';
import { __ } from '@wordpress/i18n';
import { Placeholder } from '@wordpress/components';
import { getFilteredAttributes, outputFormSelectItemWithIcon } from '../../../components/utils';

export const CalculatorResultEditor = ({
	attributes,
	setAttributes,
	formSelectOptions,
	calculatorSelectOptions,
}) => {
	const manifest = select(STORE_NAME).getBlock('calculator-result');

	const {
		blockFullName
	} = attributes;

	const {
		attributesSsr,
	} = manifest;

	const calculatorResultFormPostId = checkAttr('calculatorResultFormPostId', attributes, manifest);
	const calculatorResultFormPostIdRaw = checkAttr('calculatorResultFormPostIdRaw', attributes, manifest);
	const calculatorResultPostIdRaw = checkAttr('calculatorResultPostIdRaw', attributes, manifest);
	const calculatorResultPostId = checkAttr('calculatorResultPostId', attributes, manifest);

	if (calculatorResultPostId?.length < 1) {
		return (
			<Placeholder
					icon={icons.form}
					label={<span className='es-font-weight-400'>{__('Eightshift Forms - Calculator result', 'productive')}</span>}
					className='es-max-w-80 es-rounded-3! es-mx-auto! es-font-weight-400 es-color-cool-gray-500! es-nested-color-current!'
				>
					<AsyncSelect
						label={__('Calculator Result', 'eightshift-forms')}
						help={__('If you can\'t find you output item, start typing its name while the dropdown is open.', 'eightshift-forms')}
						value={outputFormSelectItemWithIcon(Object.keys(calculatorResultPostIdRaw).length ? calculatorResultPostIdRaw : {id: calculatorResultPostId})}
						loadOptions={calculatorSelectOptions}
						onChange={(value) => {
							setAttributes({
								[getAttrKey('calculatorResultPostIdRaw', attributes, manifest)]: {
									id: value?.id,
									label: value?.metadata?.label,
									value: value?.metadata?.value,
									metadata: value?.metadata?.metadata,
								},
								[getAttrKey('calculatorResultPostId', attributes, manifest)]: `${value?.value}`,
							});
						}}
						noBottomSpacing
					/>

					<AsyncSelect
						label={__('Form', 'eightshift-forms')}
						help={__('If you can\'t find you output item, start typing its name while the dropdown is open.', 'eightshift-forms')}
						value={outputFormSelectItemWithIcon(Object.keys(calculatorResultFormPostIdRaw).length ? calculatorResultFormPostIdRaw : {id: calculatorResultFormPostId})}
						loadOptions={formSelectOptions}
						onChange={(value) => {
							setAttributes({
								[getAttrKey('calculatorResultFormPostIdRaw', attributes, manifest)]: {
									id: value?.id,
									label: value?.metadata?.label,
									value: value?.metadata?.value,
									metadata: value?.metadata?.metadata,
								},
								[getAttrKey('calculatorResultFormPostId', attributes, manifest)]: `${value?.value}`,
							});
						}}
						noBottomSpacing
					/>
				</Placeholder>
		);
	}

	return (
		<>
			<ServerSideRender
				block={blockFullName}
				attributes={
					getFilteredAttributes(
						attributes,
						attributesSsr,
						{
							calculatorResultServerSideRender: true
						}
					)
				}
			/>
		</>
	);
};
