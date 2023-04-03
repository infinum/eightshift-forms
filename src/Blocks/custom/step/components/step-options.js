import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { StepOptions as StepOptionsComponent } from '../../../components/step/components/step-options';

export const StepOptions = ({ attributes, setAttributes }) => {
	return (
		<StepOptionsComponent
			{...props('step', attributes, {
				setAttributes,
			})}
		/>
	);
};
