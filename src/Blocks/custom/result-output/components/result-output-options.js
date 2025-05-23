import React from 'react';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey, STORE_NAME } from '@eightshift/frontend-libs-tailwind/scripts';
import { outputFormSelectItemWithIcon } from '../../../components/utils';
import { icons } from '@eightshift/ui-components/icons';
import { AsyncSelect, Toggle, ContainerPanel } from '@eightshift/ui-components';

export const ResultOutputOptions = ({ attributes, setAttributes, formSelectOptions, resultSelectOptions }) => {
	const manifest = select(STORE_NAME).getBlock('result-output');

	const resultOutputFormPostId = checkAttr('resultOutputFormPostId', attributes, manifest);
	const resultOutputFormPostIdRaw = checkAttr('resultOutputFormPostIdRaw', attributes, manifest);
	const resultOutputPostId = checkAttr('resultOutputPostId', attributes, manifest);
	const resultOutputPostIdRaw = checkAttr('resultOutputPostIdRaw', attributes, manifest);
	const resultOutputHide = checkAttr('resultOutputHide', attributes, manifest);

	return (
		<ContainerPanel title={__('Result Output', 'eightshift-forms')}>
			<AsyncSelect
				label={__('Result Output', 'eightshift-forms')}
				help={__("If you can't find your output item, try typing its name while the dropdown is open.", 'eightshift-forms')}
				value={outputFormSelectItemWithIcon(Object.keys(resultOutputPostIdRaw ?? {}).length ? resultOutputPostIdRaw : { id: resultOutputPostId })}
				loadOptions={resultSelectOptions}
				onChange={(value) => {
					setAttributes({
						[getAttrKey('resultOutputPostIdRaw', attributes, manifest)]: {
							id: value?.id,
							label: value?.metadata?.label,
							value: value?.metadata?.value,
							metadata: value?.metadata?.metadata,
						},
						[getAttrKey('resultOutputPostId', attributes, manifest)]: `${value?.value.toString()}`,
					});
				}}
			/>

			<AsyncSelect
				label={__('Connected Form', 'eightshift-forms')}
				help={__("If you can't find your connected form, try typing its name while the dropdown is open.", 'eightshift-forms')}
				value={outputFormSelectItemWithIcon(Object.keys(resultOutputFormPostIdRaw ?? {}).length ? resultOutputFormPostIdRaw : { id: resultOutputFormPostId })}
				loadOptions={formSelectOptions}
				onChange={(value) => {
					setAttributes({
						[getAttrKey('resultOutputFormPostIdRaw', attributes, manifest)]: {
							id: value?.id,
							label: value?.metadata?.label,
							value: value?.metadata?.value,
							metadata: value?.metadata?.metadata,
						},
						[getAttrKey('resultOutputFormPostId', attributes, manifest)]: `${value?.value.toString()}`,
					});
				}}
			/>

			<Toggle
				icon={icons.visibilityAlt}
				label={__('Hide by default', 'eightshift-forms')}
				help={__('Hide result output block by default. It will be shown only on form success.', 'eightshift-forms')}
				checked={resultOutputHide}
				onChange={(value) => setAttributes({ [getAttrKey('resultOutputHide', attributes, manifest)]: value })}
			/>
		</ContainerPanel>
	);
};
