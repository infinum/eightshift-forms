import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { InputOptions as InputOptionsComponent } from '../../../components/input/components/input-options';

export const InputOptions = ({ attributes, setAttributes, clientId }) => {
	return (
		<InputOptionsComponent
			{...props('input', attributes, {
				setAttributes,
			})}
		/>
	);
};
