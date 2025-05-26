import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { props, checkAttr, STORE_NAME, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { MissingName, preventSaveOnMissingProps } from './../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';

export const FileEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('file');

	const { blockClientId } = attributes;

	const fileName = checkAttr('fileName', attributes, manifest);
	const fileCustomInfoText = checkAttr('fileCustomInfoText', attributes, manifest);
	const fileCustomInfoTextUse = checkAttr('fileCustomInfoTextUse', attributes, manifest);
	const fileCustomInfoButtonText = checkAttr('fileCustomInfoButtonText', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('fileName', attributes, manifest), fileName);

	const file = (
		<>
			<div
				className={'es:min-h-10 es:w-full es:p-4 es:border es:border-secondary-300 es:bg-white es:p-2 es:text-sm es:flex es:items-center es:justify-center es:gap-2 es:flex-col'}
			>
				{fileCustomInfoTextUse && <div className={'es:text-sm es:text-secondary-400'}>{fileCustomInfoText}</div>}
				{!fileCustomInfoTextUse && <div className={'es:text-sm es:text-secondary-400'}>{__('Drag and drop files here', 'eightshift-forms')}</div>}

				<div className={'es:px-3 es:py-1 es:text-center es:text-base es:font-medium es:bg-accent-600 es:text-white'}>
					{fileCustomInfoButtonText?.length > 0 ? fileCustomInfoButtonText : __('Add files', 'eightshift-forms')}
				</div>
			</div>

			<MissingName value={fileName} />

			{fileName && <ConditionalTagsEditor {...props('conditionalTags', attributes)} />}
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: file,
					fieldIsRequired: checkAttr('fileIsRequired', attributes, manifest),
				})}
			/>
		</>
	);
};
