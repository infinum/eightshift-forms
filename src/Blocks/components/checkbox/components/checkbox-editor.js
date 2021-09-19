import React from 'react';
import { checkAttr, props } from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';
import manifest from './../manifest.json';


export const CheckboxEditor = (attributes) => {

	const checkboxContent = checkAttr('checkboxContent', attributes, manifest);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: checkboxContent
				})}
			/>
		</>
	);
}
