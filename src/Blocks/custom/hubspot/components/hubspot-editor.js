import React from 'react';
import { ServerSideRender } from '@eightshift/frontend-libs/scripts';

export const HubspotEditor = ({ attributes, postId }) => {
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
						hubspotFormServerSideRender: true,
						hubspotFormPostId: postId.toString(),
					}
				}
			/>
		</div>
	);
};
