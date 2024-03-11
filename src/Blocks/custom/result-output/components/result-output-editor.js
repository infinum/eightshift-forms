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

export const ResultOutputEditor = ({
	attributes,
	setAttributes,
	formSelectOptions,
	resultSelectOptions,
}) => {
	const manifest = select(STORE_NAME).getBlock('result-output');

	const {
		blockFullName
	} = attributes;

	const {
		attributesSsr,
	} = manifest;

	const resultOutputFormPostId = checkAttr('resultOutputFormPostId', attributes, manifest);
	const resultOutputFormPostIdRaw = checkAttr('resultOutputFormPostIdRaw', attributes, manifest);
	const resultOutputPostIdRaw = checkAttr('resultOutputPostIdRaw', attributes, manifest);
	const resultOutputPostId = checkAttr('resultOutputPostId', attributes, manifest);

	if (resultOutputPostId?.length < 1 || resultOutputFormPostId?.length < 1) {
		return (
			<Placeholder
					icon={icons.form}
					label={<span className='es-font-weight-400'>{__('Eightshift Forms - Result output', 'eightshift-forms')}</span>}
					className='es-max-w-80 es-rounded-3! es-mx-auto! es-font-weight-400 es-color-cool-gray-500! es-nested-color-current!'
				>
					<AsyncSelect
						label={__('Result Output', 'eightshift-forms')}
						help={__('If you can\'t find your output item, try typing its name while the dropdown is open.', 'eightshift-forms')}
						value={outputFormSelectItemWithIcon(Object.keys(resultOutputPostIdRaw).length ? resultOutputPostIdRaw : {id: resultOutputPostId})}
						loadOptions={resultSelectOptions}
						onChange={(value) => {
							setAttributes({
								[getAttrKey('resultOutputPostIdRaw', attributes, manifest)]: {
									id: value?.id,
									label: value?.metadata?.label,
									value: value?.metadata?.value,
									metadata: value?.metadata?.metadata,
								},
								[getAttrKey('resultOutputPostId', attributes, manifest)]: `${value?.value}`,
							});
						}}
						noBottomSpacing
					/>

					<AsyncSelect
						label={__('Connected Form', 'eightshift-forms')}
						help={__('If you can\'t find your connected form, try typing its name while the dropdown is open.', 'eightshift-forms')}
						value={outputFormSelectItemWithIcon(Object.keys(resultOutputFormPostIdRaw).length ? resultOutputFormPostIdRaw : {id: resultOutputFormPostId})}
						loadOptions={formSelectOptions}
						onChange={(value) => {
							setAttributes({
								[getAttrKey('resultOutputFormPostIdRaw', attributes, manifest)]: {
									id: value?.id,
									label: value?.metadata?.label,
									value: value?.metadata?.value,
									metadata: value?.metadata?.metadata,
								},
								[getAttrKey('resultOutputFormPostId', attributes, manifest)]: `${value?.value}`,
							});
						}}
						noBottomSpacing
					/>
				</Placeholder>
		);
	}

	return (
			<ServerSideRender
				block={blockFullName}
				attributes={
					getFilteredAttributes(
						attributes,
						attributesSsr,
						{
							resultOutputServerSideRender: true
						}
					)
				}
			/>
	);
};
