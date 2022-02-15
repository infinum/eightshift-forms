import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { RadiosOptions as RadiosOptionsComponent } from '../../../components/radios/components/radios-options';

export const RadiosOptions = ({ attributes, setAttributes }) => {
	return (
		<RadiosOptionsComponent
			{...props('radios', attributes, {
				setAttributes,
			})}
		/>
	);
};
