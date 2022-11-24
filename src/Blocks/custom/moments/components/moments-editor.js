import React from 'react';
import { ServerSideRender } from '@eightshift/frontend-libs/scripts';

export const MomentsEditor = ({ attributes, postId }) => {
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
						momentsFormServerSideRender: true,
						momentsFormPostId: postId.toString(),
					}
				}
			/>
		</div>
	);
};
