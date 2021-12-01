import React from 'react';
import { ServerSideRender } from '@eightshift/frontend-libs/scripts';

export const GoodbitsEditor = ({ attributes, postId }) => {
	const {
		blockClass,
		blockFullName,
	} = attributes;

	return (
		<div className={blockClass}>
			<ServerSideRender
				block={blockFullName}
				attributes={
					{
						...attributes,
						goodbitsFormServerSideRender: true,
						goodbitsFormPostId: postId.toString(),
					}
				}
			/>
		</div>
	);
};
