import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { SubmitEditor } from './components/submit-editor';
import { SubmitOptions } from './components/submit-options';

export const Submit = (props) => {
	return (
		<>
			<InspectorControls>
				<SubmitOptions {...props} />
			</InspectorControls>
			<SubmitEditor {...props} />
		</>
	);
};
