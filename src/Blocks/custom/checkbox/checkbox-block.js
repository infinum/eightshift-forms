import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { CheckboxEditor } from './components/checkbox-editor';
import { CheckboxOptions } from './components/checkbox-options';

export const Checkbox = (props) => {
	return (
		<>
			<InspectorControls>
				<CheckboxOptions {...props} />
			</InspectorControls>
			<CheckboxEditor {...props} />
		</>
	);
};
