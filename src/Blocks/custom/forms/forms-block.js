import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { FormsEditor } from './components/forms-editor';
import { FormsOptions } from './components/forms-options';

export const Forms = (props) => {
	return (
		<>
			<InspectorControls>
				<FormsOptions {...props} />
			</InspectorControls>
			<FormsEditor {...props} />
		</>
	);
};
