import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { DynamicEditor } from './components/dynamic-editor';
import { DynamicOptions } from './components/dynamic-options';

export const Dynamic = (props) => {
	return (
		<>
			<InspectorControls>
				<DynamicOptions {...props} />
			</InspectorControls>
			<DynamicEditor {...props} />
		</>
	);
};
