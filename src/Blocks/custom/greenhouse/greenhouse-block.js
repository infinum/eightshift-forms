import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { GreenhouseEditor } from './components/greenhouse-editor';
import { GreenhouseOptions } from './components/greenhouse-options';

export const Greenhouse = (props) => {
	return (
		<>
			<InspectorControls>
				<GreenhouseOptions {...props} />
			</InspectorControls>
			<GreenhouseEditor {...props} />
		</>
	);
};
