import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { CheckboxesOptions as CheckboxesOptionsComponent } from '../../../components/checkboxes/components/checkboxes-options';

export const CheckboxesOptions = ({ attributes, setAttributes, clientId }) => {
	return (
		<CheckboxesOptionsComponent
			{...props('checkboxes', attributes, {
				setAttributes,
				clientId
			})}
		/>
	);
};
