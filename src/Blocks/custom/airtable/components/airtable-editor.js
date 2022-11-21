import React from 'react';
import { ServerSideRender } from '@eightshift/frontend-libs/scripts';

export const AirtableEditor = ({ attributes, postId }) => {
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
						airtableFormServerSideRender: true,
						airtableFormPostId: postId.toString(),
					}
				}
			/>
		</div>
	);
};
