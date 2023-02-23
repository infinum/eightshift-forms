import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { MailerliteEditor } from './components/mailerlite-editor';
import { MailerliteOptions } from './components/mailerlite-options';

export const Mailerlite = (props) => {
	const itemIdKey = 'mailerliteIntegrationId';

	return (
		<>
			<InspectorControls>
				<MailerliteOptions
					{...props}
					clientId={props.clientId}
					itemIdKey={itemIdKey}
				/>
			</InspectorControls>
			<MailerliteEditor
				{...props}
				itemIdKey={itemIdKey}
			/>
		</>
	);
};
