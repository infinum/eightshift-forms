import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { SelectOptions as SelectOptionsComponent } from '../../../components/select/components/select-options';

export const SelectOptions = ({ attributes, setAttributes }) => {
	return (
		<SelectOptionsComponent
			{...props('select', attributes, {
				setAttributes,
			})}
		/>
	);
};
