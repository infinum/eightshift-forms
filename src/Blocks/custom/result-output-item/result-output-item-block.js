import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { ResultOutputItemEditor } from './components/result-output-item-editor';
import { ResultOutputItemOptions } from './components/result-output-item-options';

export const ResultOutputItem = (props) => {
	return (
		<>
			<InspectorControls>
				<ResultOutputItemOptions {...props} />
			</InspectorControls>
			<ResultOutputItemEditor {...props} />
		</>
	);
};
