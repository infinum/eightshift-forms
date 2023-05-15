import React from 'react';
import { __ } from '@wordpress/i18n';
import {
	props,
	checkAttr,
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { getAdditionalContentFilterContent, MissingName } from './../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';
import manifest from '../manifest.json';

export const FileEditor = (attributes) => {
	const {
		componentClass,
		componentName
	} = manifest;

	const {
		additionalFieldClass,
	} = attributes;

	const fileName = checkAttr('fileName', attributes, manifest);
	const fileCustomInfoText = checkAttr('fileCustomInfoText', attributes, manifest);
	const fileCustomInfoTextUse = checkAttr('fileCustomInfoTextUse', attributes, manifest);
	const fileCustomInfoButtonText = checkAttr('fileCustomInfoButtonText', attributes, manifest);

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

			<div dangerouslySetInnerHTML={{ __html: getAdditionalContentFilterContent(componentName) }} />
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
