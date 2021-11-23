import React from 'react';
import { useSelect } from "@wordpress/data";
import { InspectorControls } from '@wordpress/block-editor';
import { MailerliteEditor } from './components/mailerlite-editor';
import { MailerliteOptions } from './components/mailerlite-options';

export const Mailerlite = (props) => {
	const postId = useSelect((select) => select('core/editor').getCurrentPostId());

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
				postId={postId}
			/>
		</>
	);
};
