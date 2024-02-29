import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { SliderEditor as SliderEditorComponent } from '../../../components/slider/components/slider-editor';

export const SliderEditor = ({ attributes, setAttributes, clientId }) => {
	return (
		<SliderEditorComponent
			{...props('slider', attributes, {
				setAttributes,
				clientId,
			})}
		/>
	);
};
