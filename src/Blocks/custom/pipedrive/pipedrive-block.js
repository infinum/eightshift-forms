import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { PipedriveEditor } from './components/pipedrive-editor';
import { PipedriveOptions } from './components/pipedrive-options';

export const Pipedrive = (props) => {
	return (
		<>
			<InspectorControls>
				<PipedriveOptions {...props} />
			</InspectorControls>
			<PipedriveEditor {...props} />
		</>
	);
};
