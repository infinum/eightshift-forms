import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { SelectOptionOptions as SelectOptionOptionsComponent } from '../../../components/select-option/components/select-option-options';

export const SelectOptionOptions = ({ attributes, setAttributes }) => {
	return (
		<SelectOptionOptionsComponent
			{...props('selectOption', attributes, {
				setAttributes,
			})}
		/>
	);
};
