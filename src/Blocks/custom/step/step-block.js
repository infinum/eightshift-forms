import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { StepEditor } from './components/step-editor';
import { StepOptions } from './components/step-options';

export const Step = (props) => {
	return (
		<>
			<InspectorControls>
				<StepOptions {...props} />
			</InspectorControls>
			<StepEditor {...props} />
		</>
	);
};
