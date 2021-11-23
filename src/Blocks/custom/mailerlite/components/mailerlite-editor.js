import React from 'react';
import { ServerSideRender } from '@eightshift/frontend-libs/scripts';

export const MailerliteEditor = ({ attributes, postId }) => {
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
						mailerliteServerSideRender: true,
						mailerliteFormPostId: postId.toString(),
					}
				}
			/>
		</div>
	);
};
