import React from 'react';
import { props } from '@eightshift/frontend-libs-tailwind/scripts';
import { CheckboxEditor as CheckboxEditorComponent } from '../../../components/checkbox/components/checkbox-editor';

export const CheckboxEditor = ({ attributes, setAttributes }) => {
	return (
		<CheckboxEditorComponent
			{...props('checkbox', attributes, {
				setAttributes,
			})}
		/>
	);
};
