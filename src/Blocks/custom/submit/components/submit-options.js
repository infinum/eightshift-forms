import React from 'react';
import { props } from '@eightshift/frontend-libs-tailwind/scripts';
import { SubmitOptions as SubmitOptionsComponent } from '../../../components/submit/components/submit-options';

export const SubmitOptions = ({ attributes, setAttributes }) => {
	return (
		<SubmitOptionsComponent
			{...props('submit', attributes, {
				setAttributes,
			})}
		/>
	);
};
