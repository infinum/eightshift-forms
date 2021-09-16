import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { InputEditor } from './components/input-editor';
import { InputOptions } from './components/input-options';

export const Input = (props) => {
	return (
		<>
			<InspectorControls>
				<InputOptions {...props} />
			</InspectorControls>
			<InputEditor {...props} />
		</>
	);
};
