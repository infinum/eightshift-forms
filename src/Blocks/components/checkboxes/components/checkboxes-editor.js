import React from 'react';
import {
	checkAttr,
	props,
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { getAdditionalContentFilterContent, MissingName } from './../../utils';
import manifest from '../manifest.json';

export const CheckboxesEditor = (attributes) => {
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
