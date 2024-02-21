import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { DynamicOptions as DynamicOptionsComponent } from '../../../components/dynamic/components/dynamic-options';

export const DynamicOptions = ({ attributes, setAttributes }) => {
	return (
		<DynamicOptionsComponent
			{...props('dynamic', attributes, {
				setAttributes,
			})}
		/>
	);
};
