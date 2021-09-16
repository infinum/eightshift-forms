import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { TextareaEditor } from './components/textarea-editor';
import { TextareaOptions } from './components/textarea-options';

export const Textarea = (props) => {
	return (
		<>
			<InspectorControls>
				<TextareaOptions {...props} />
			</InspectorControls>
			<TextareaEditor {...props} />
		</>
	);
};
