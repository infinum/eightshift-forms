import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { MailchimpEditor } from './components/mailchimp-editor';
import { MailchimpOptions } from './components/mailchimp-options';

export const Mailchimp = (props) => {
	return (
		<>
			<InspectorControls>
				<MailchimpOptions {...props} />
			</InspectorControls>
			<MailchimpEditor {...props} />
		</>
	);
};
