import React from 'react';
import { props } from '@eightshift/frontend-libs-tailwind/scripts';
import { SelectOptionEditor as SelectOptionEditorComponent } from '../../../components/select-option/components/select-option-editor';

export const SelectOptionEditor = ({ attributes, setAttributes }) => {
	return (
		<SelectOptionEditorComponent
			{...props('selectOption', attributes, {
				setAttributes,
			})}
		/>
	);
};
