import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { NationbuilderEditor } from './components/nationbuilder-editor';
import { NationbuilderOptions } from './components/nationbuilder-options';

export const Nationbuilder = (props) => {
	return (
		<>
			<InspectorControls>
				<NationbuilderOptions {...props} />
			</InspectorControls>
			<NationbuilderEditor {...props} />
		</>
	);
};
