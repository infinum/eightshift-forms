import React from 'react';
import { ServerSideRender } from '@eightshift/frontend-libs/scripts';

export const ActiveCampaignEditor = ({ attributes, postId }) => {
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
						activeCampaignFormServerSideRender: true,
						activeCampaignFormPostId: postId.toString(),
					}
				}
			/>
		</div>
	);
};
