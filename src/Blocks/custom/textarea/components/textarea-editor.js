import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { TextareaEditor as TextareaEditorComponent } from '../../../components/textarea/components/textarea-editor';

export const TextareaEditor = ({ attributes, setAttributes }) => {

	const {
		blockClass,
	} = attributes;

	return (
		<div className={blockClass}>
			<TextareaEditorComponent
				{...props('textarea', attributes, {
					setAttributes: setAttributes,
				})}
			/>
		</div>
	);
}
