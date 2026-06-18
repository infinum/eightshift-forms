import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { PardotEditor } from './components/pardot-editor';
import { PardotOptions } from './components/pardot-options';

export const Pardot = (props) => {
	return (
		<>
			<InspectorControls>
				<PardotOptions {...props} />
			</InspectorControls>
			<PardotEditor {...props} />
		</>
	);
};
