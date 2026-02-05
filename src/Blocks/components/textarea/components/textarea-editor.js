import React from 'react';
import { checkAttr, props, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';
import { preventSaveOnMissingProps, StatusIconConditionals, StatusIconMissingName } from './../../utils';
import manifest from '../manifest.json';

export const TextareaEditor = (attributes) => {
	const { blockClientId, prefix } = attributes;

	const textareaValue = checkAttr('textareaValue', attributes, manifest);
	const textareaPlaceholder = checkAttr('textareaPlaceholder', attributes, manifest);
	const textareaName = checkAttr('textareaName', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('textareaName', attributes, manifest), textareaName);

	const textarea = (
		<>
			<textarea
				className='esf-input'
				placeholder={textareaPlaceholder}
				readOnly
			>
				{textareaValue}
			</textarea>
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: textarea,
					fieldIsRequired: checkAttr('textareaIsRequired', attributes, manifest),
				})}
				statusSlog={[
					!textareaName && <StatusIconMissingName />,
					attributes?.[`${prefix}ConditionalTagsUse`] && <StatusIconConditionals />,
				]}
			/>
		</>
	);
};
