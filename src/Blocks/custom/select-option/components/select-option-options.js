import React from 'react';
import { __ } from '@wordpress/i18n';
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
