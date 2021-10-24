import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { SenderEmailEditor } from './components/sender-email-editor';
import { SenderEmailOptions } from './components/sender-email-options';

export const SederEmail = (props) => {
	return (
		<>
			<InspectorControls>
				<SenderEmailOptions {...props} />
			</InspectorControls>
			<SenderEmailEditor {...props} />
		</>
	);
};
