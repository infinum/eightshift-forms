import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { MailerliteEditor } from './components/mailerlite-editor';
import { MailerliteOptions } from './components/mailerlite-options';

export const Mailerlite = (props) => {
	return (
		<>
			<InspectorControls>
				<MailerliteOptions {...props} />
			</InspectorControls>
			<MailerliteEditor {...props} />
		</>
	);
};
