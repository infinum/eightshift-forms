import React from 'react';
import { ServerSideRender } from '@eightshift/frontend-libs/scripts';

export const MailchimpEditor = ({ attributes, postId }) => {
	const {
		blockClass,
		blockFullName
	} = attributes;

	return (
		<div className={blockClass}>
			<ServerSideRender
				block={blockFullName}
				attributes={
					{
						...attributes,
						mailchimpServerSideRender: true,
						mailchimpFormPostId: postId.toString(),
					}
				}
			/>
		</div>
	);
};
