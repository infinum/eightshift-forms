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

	const file = (
		<>
			<div className={componentClass}>{__('File upload', 'eightshift-forms')}</div>

			<MissingName value={fileName} isEditor={true} />

			<ConditionalTagsEditor
				{...props('conditionalTags', attributes)}
			/>

			<div dangerouslySetInnerHTML={{__html: getAdditionalContentFilterContent(componentName)}} />
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: file,
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
