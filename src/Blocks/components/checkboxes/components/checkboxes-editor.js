/* global esFormsLocalization */

import React from 'react';
import {
	checkAttr,
	props,
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { getAdditionalContentFilterContent } from './../../utils';
import manifest from '../manifest.json';


export const CheckboxesEditor = (attributes) => {
	const {
		componentName
	} = manifest;

	const {
		additionalFieldClass,
	} = attributes;

	const checkboxesContent = checkAttr('checkboxesContent', attributes, manifest);

	const checkboxes = (
		<>
			{checkboxesContent}
			<div dangerouslySetInnerHTML={{__html: getAdditionalContentFilterContent(componentName)}} />
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: checkboxes,
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
