import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { MailerEditor } from './components/mailer-editor';
import { MailerOptions } from './components/mailer-options';

export const Mailer = (props) => {
	return (
		<>
			<InspectorControls>
				<MailerOptions {...props} />
			</InspectorControls>
			<MailerEditor {...props} />
		</>
	);
};
