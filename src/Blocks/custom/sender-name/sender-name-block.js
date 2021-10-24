import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { SenderNameEditor } from './components/sender-name-editor';
import { SenderNameOptions } from './components/sender-name-options';

export const SederName = (props) => {
	return (
		<>
			<InspectorControls>
				<SenderNameOptions {...props} />
			</InspectorControls>
			<SenderNameEditor {...props} />
		</>
	);
};
