import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { CheckboxOptions as CheckboxOptionsComponent } from '../../../components/checkbox/components/checkbox-options';

export const CheckboxOptions = ({ attributes, setAttributes }) => {
	return (
		<CheckboxOptionsComponent
			{...props('checkbox', attributes, {
				setAttributes,
			})}
		/>
	);
};
