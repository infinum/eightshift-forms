import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { RadioOptions as RadioOptionsComponent } from '../../../components/radio/components/radio-options';

export const RadioOptions = ({ attributes, setAttributes }) => {
	return (
		<RadioOptionsComponent
			{...props('radio', attributes, {
				setAttributes,
			})}
		/>
	);
};
