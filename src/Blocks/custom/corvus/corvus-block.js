import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { CorvusEditor } from './components/corvus-editor';
import { CorvusOptions } from './components/corvus-options';

export const Corvus = (props) => {
	return (
		<>
			<InspectorControls>
				<CorvusOptions {...props} />
			</InspectorControls>
			<CorvusEditor {...props} />
		</>
	);
};
