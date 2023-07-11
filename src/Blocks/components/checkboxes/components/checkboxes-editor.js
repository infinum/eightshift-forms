import React from 'react';
import { select } from '@wordpress/data';
import {
	STORE_NAME,
	checkAttr,
	props,
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { getAdditionalContentFilterContent, MissingName } from './../../utils';
import { ConditionalTagsEditor } from '../../conditional-tags/components/conditional-tags-editor';

export const CheckboxesEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('checkboxes');

	const {
		componentName
	} = manifest;

	const {
		additionalFieldClass,
	} = attributes;

	const checkboxesContent = checkAttr('checkboxesContent', attributes, manifest);
	const checkboxesName = checkAttr('checkboxesName', attributes, manifest);

	const checkboxes = (
		<>
			{checkboxesContent}

			<MissingName value={checkboxesName} />

			{checkboxesName &&
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
					fieldContent: checkboxes,
					fieldIsRequired: checkAttr('checkboxesIsRequired', attributes, manifest),
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
