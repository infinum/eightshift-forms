import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { RadiosEditor } from './components/radios-editor';
import { RadiosOptions } from './components/radios-options';

export const Radios = (props) => {
	return (
		<>
			<InspectorControls>
				<RadiosOptions {...props} />
			</InspectorControls>
			<RadiosEditor {...props} />
		</>
	);
};
