import React from 'react';
import { checkAttr, props } from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../field/components/field-editor';
import manifest from '../manifest.json';


export const RadioEditor = (attributes) => {

	const radioContent = checkAttr('radioContent', attributes, manifest);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: radioContent
				})}
			/>
		</>
	);
}
