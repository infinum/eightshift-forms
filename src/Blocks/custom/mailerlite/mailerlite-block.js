import React from 'react';
import { select } from "@wordpress/data";
import { InspectorControls } from '@wordpress/block-editor';
import { MailerliteEditor } from './components/mailerlite-editor';
import { MailerliteOptions } from './components/mailerlite-options';

export const Mailerlite = (props) => {
	const postId = select('core/editor').getCurrentPostId();

	return (
		<>
			<InspectorControls>
				<MailerliteOptions
					{...props}
					postId={postId}
				/>
			</InspectorControls>
			<MailerliteEditor
				{...props}
			/>
		</>
	);
};
