import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { GoodbitsEditor } from './components/goodbits-editor';
import { GoodbitsOptions } from './components/goodbits-options';

export const Goodbits = (props) => {
	return (
		<>
			<InspectorControls>
				<GoodbitsOptions {...props} />
			</InspectorControls>
			<GoodbitsEditor {...props} />
		</>
	);
};
