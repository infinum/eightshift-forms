import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { FormSelectorEditor } from './components/form-selector-editor';
import { FormSelectorOptions } from './components/form-selector-options';

export const FormSelector = (props) => {
	return (
		<>
			<InspectorControls>
				<FormSelectorOptions {...props} />
			</InspectorControls>
			<FormSelectorEditor {...props} />
		</>
	);
};
