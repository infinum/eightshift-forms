import React from 'react';
import { props } from '@eightshift/frontend-libs-tailwind/scripts';
import { RadioEditor as RadioEditorComponent } from '../../../components/radio/components/radio-editor';

export const RadioEditor = ({ attributes, setAttributes }) => {
	return (
		<RadioEditorComponent
			{...props('radio', attributes, {
				setAttributes,
			})}
		/>
	);
};
