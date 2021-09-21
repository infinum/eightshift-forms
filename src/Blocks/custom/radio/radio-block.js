import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { RadioEditor } from './components/radio-editor';
import { RadioOptions } from './components/radio-options';

export const Radio = (props) => {
	return (
		<>
			<InspectorControls>
				<RadioOptions {...props} />
			</InspectorControls>
			<RadioEditor {...props} />
		</>
	);
};
