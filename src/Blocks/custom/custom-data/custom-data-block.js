import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { CustomDataEditor } from './components/custom-data-editor';
import { CustomDataOptions } from './components/custom-data-options';

export const CustomData = (props) => {
	return (
		<>
			<InspectorControls>
				<CustomDataOptions {...props} />
			</InspectorControls>
			<CustomDataEditor {...props} />
		</>
	);
};
