import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { CheckboxesEditor } from './components/checkboxes-editor';
import { CheckboxesOptions } from './components/checkboxes-options';

export const Checkboxes = (props) => {
	return (
		<>
			<InspectorControls>
				<CheckboxesOptions {...props} />
			</InspectorControls>
			<CheckboxesEditor {...props} />
		</>
	);
};
