import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import {
	props,
	checkAttr,
	STORE_NAME,
	getAttrKey,
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { MissingName, preventSaveOnMissingProps } from './../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';

export const FileEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('file');

	const {
		componentClass,
		componentName
	} = manifest;

	const {
		additionalFieldClass,
		blockClientId,
	} = attributes;

	const fileName = checkAttr('fileName', attributes, manifest);
	const fileCustomInfoText = checkAttr('fileCustomInfoText', attributes, manifest);
	const fileCustomInfoTextUse = checkAttr('fileCustomInfoTextUse', attributes, manifest);
	const fileCustomInfoButtonText = checkAttr('fileCustomInfoButtonText', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('fileName', attributes, manifest), fileName);

	const file = (
		<>
			<div className={`${componentClass}__custom-wrap`}>
				{fileCustomInfoTextUse && fileCustomInfoText}
				{!fileCustomInfoTextUse && __('Drag and drop files here', 'eightshift-forms')}


				<div className={`${componentClass}__button`}>
					{fileCustomInfoButtonText?.length > 0 ? fileCustomInfoButtonText : __('Add files', 'eightshift-forms')}
				</div>
			</div>

			<MissingName value={fileName} />

			{fileName &&
				<ConditionalTagsEditor
					{...props('conditionalTags', attributes)}
				/>
			}
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: file,
					fieldIsRequired: checkAttr('fileIsRequired', attributes, manifest),
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
