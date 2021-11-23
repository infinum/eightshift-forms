import React from 'react';
import { useSelect } from "@wordpress/data";
import { InspectorControls } from '@wordpress/block-editor';
import { MailchimpEditor } from './components/mailchimp-editor';
import { MailchimpOptions } from './components/mailchimp-options';

export const Mailchimp = (props) => {
	const postId = useSelect((select) => select('core/editor').getCurrentPostId());

	return (
		<>
			<InspectorControls>
				<MailchimpOptions
					{...props}
					postId={postId}
				/>
			</InspectorControls>
			<MailchimpEditor
				{...props}
				postId={postId}
			/>
		</>
	);
};
