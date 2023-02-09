import React from 'react';
import { ServerSideRender } from '@eightshift/frontend-libs/scripts';

export const WorkableEditor = ({ attributes, postId }) => {
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
						workableFormServerSideRender: true,
						workableFormPostId: postId.toString(),
					}
				}
			/>
		</div>
	);
};
