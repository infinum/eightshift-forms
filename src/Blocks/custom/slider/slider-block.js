import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { SliderEditor } from './components/slider-editor';
import { SliderOptions } from './components/slider-options';

export const Slider = (props) => {
	return (
		<>
			<InspectorControls>
				<SliderOptions {...props} />
			</InspectorControls>
			<SliderEditor {...props} />
		</>
	);
};
