import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { SelectEditor } from './components/select-editor';
import { SelectOptions } from './components/select-options';

export const Select = (props) => {
	return (
		<>
			<InspectorControls>
				<SelectOptions {...props} />
			</InspectorControls>
			<SelectEditor {...props} />
		</>
	);
};
