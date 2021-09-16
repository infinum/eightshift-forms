import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { SelectOptionEditor } from './components/select-option-editor';
import { SelectOptionOptions } from './components/select-option-options';

export const SelectOption = (props) => {
	return (
		<>
			<InspectorControls>
				<SelectOptionOptions {...props} />
			</InspectorControls>
			<SelectOptionEditor {...props} />
		</>
	);
};
