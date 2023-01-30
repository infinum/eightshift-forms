import React from 'react';
import {
	checkAttr,
	props,
} from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import { getAdditionalContentFilterContent } from './../../utils';
import manifest from '../manifest.json';

export const RadiosEditor = (attributes) => {
	const {
		componentName
	} = manifest;

	const {
		additionalFieldClass,
	} = attributes;

	const radiosContent = checkAttr('radiosContent', attributes, manifest);

	const radios = (
		<>
			{radiosContent}
			<div dangerouslySetInnerHTML={{__html: getAdditionalContentFilterContent(componentName)}} />
		</>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: radios
				})}
				additionalFieldClass={additionalFieldClass}
				selectorClass={componentName}
			/>
		</>
	);
};
