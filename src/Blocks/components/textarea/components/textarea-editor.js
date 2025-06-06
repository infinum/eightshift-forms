import React from 'react';
import { select } from '@wordpress/data';
import { checkAttr, props, STORE_NAME, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';
import { MissingName, preventSaveOnMissingProps } from './../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';

export const TextareaEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('textarea');

	const { blockClientId } = attributes;

	const textareaValue = checkAttr('textareaValue', attributes, manifest);
	const textareaPlaceholder = checkAttr('textareaPlaceholder', attributes, manifest);
	const textareaName = checkAttr('textareaName', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('textareaName', attributes, manifest), textareaName);

	const textarea = (
		<>
			<textarea
				placeholder={textareaPlaceholder}
				readOnly
				className={'es:min-h-10 es:w-full es:border es:border-secondary-300 es:bg-white es:p-2 es:text-sm'}
			>
				{textareaValue}
			</textarea>

			<MissingName value={textareaName} />

			{textareaName && <ConditionalTagsEditor {...props('conditionalTags', attributes)} />}
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: textarea,
					fieldIsRequired: checkAttr('textareaIsRequired', attributes, manifest),
				})}
			/>
		</>
	);
};
