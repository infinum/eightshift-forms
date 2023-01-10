import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { MailchimpEditor } from './components/mailchimp-editor';
import { MailchimpOptions } from './components/mailchimp-options';

export const Mailchimp = (props) => {
	const itemIdKey = 'mailchimpIntegrationId';

	return (
		<>
			<InspectorControls>
				<MailchimpOptions
					{...props}
					clientId={props.clientId}
					itemIdKey={itemIdKey}
				/>
			</InspectorControls>
			<MailchimpEditor
				{...props}
				itemIdKey={itemIdKey}
			/>
		</>
	);
};
