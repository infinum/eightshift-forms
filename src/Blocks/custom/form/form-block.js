import React from 'react';
import { useSelect } from "@wordpress/data";
import { InspectorControls } from '@wordpress/block-editor';
import { FormEditor } from './components/form-editor';
import { FormOptions } from './components/form-options';

export const Form = (props) => {
	return (
		<>
			<InspectorControls>
				<FormOptions {...props} />
			</InspectorControls>
			<FormEditor {...props} />
		</>
	);
};
