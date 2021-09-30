import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { InputEditor as InputEditorComponent } from '../../../components/input/components/input-editor';

export const InputEditor = ({ attributes, setAttributes, clientId }) => {

	const {
		blockClass,
	} = attributes;

	return (
		<InputEditorComponent
			{...props('input', attributes, {
				blockClass,
				setAttributes: setAttributes,
				clientId,
			})}
		/>
	);
}
