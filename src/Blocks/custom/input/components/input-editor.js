import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { InputEditor as InputEditorComponent } from '../../../components/input/components/input-editor';

export const InputEditor = ({ attributes, setAttributes }) => {

	const {
		blockClass,
	} = attributes;

	return (
		<div className={blockClass}>
			<InputEditorComponent
				{...props('input', attributes, {
					setAttributes: setAttributes,
				})}
			/>
		</div>
	);
}
