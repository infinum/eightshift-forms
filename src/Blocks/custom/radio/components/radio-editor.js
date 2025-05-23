import React from 'react';
import { props } from '@eightshift/frontend-libs-tailwind/scripts';
import { RadioEditor as RadioEditorComponent } from '../../../components/radio/components/radio-editor';

export const RadioEditor = ({ attributes, setAttributes }) => {
	const { blockClass } = attributes;

	return (
		<RadioEditorComponent
			{...props('radio', attributes, {
				setAttributes,
			})}
		/>
	);
};
