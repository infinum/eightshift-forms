import React from 'react';
import { ServerSideRender } from '@eightshift/frontend-libs/scripts';

export const GreenhouseEditor = ({ attributes, postId }) => {
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
						greenhouseServerSideRender: true,
						greenhouseFormPostId: postId.toString(),
					}
				}
			/>
		</div>
	);
};
