import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { SliderOptions as SliderOptionsComponent } from '../../../components/slider/components/slider-options';

export const SliderOptions = ({ attributes, setAttributes }) => {
	return (
		<SliderOptionsComponent
			{...props('slider', attributes, {
				setAttributes,
			})}
		/>
	);
};
