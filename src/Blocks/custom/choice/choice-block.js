import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { ChoiceEditor } from './components/choice-editor';
import { ChoiceOptions } from './components/choice-options';

export const Choice = (props) => {
	return (
		<>
			<InspectorControls>
				<ChoiceOptions {...props} />
			</InspectorControls>
			<ChoiceEditor {...props} />
		</>
	);
};
