import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { FormEditor as FormEditorComponent } from '../../../components/form/components/form-editor';

export const FormEditor = ({ attributes, setAttributes }) => {

	const {
		blockClass,
	} = attributes;

	return (
		<div className={blockClass}>
			<FormEditorComponent
				{...props('form', attributes, {
					setAttributes: setAttributes,
				})}
			/>
		</div>
	);
}
