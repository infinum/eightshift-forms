import React from 'react';
import { select } from "@wordpress/data";
import { InspectorControls } from '@wordpress/block-editor';
import { ActiveCampaignEditor } from './components/active-campaign-editor';
import { ActiveCampaignOptions } from './components/active-campaign-options';

export const ActiveCampaign = (props) => {
	const postId = select('core/editor').getCurrentPostId();

	return (
		<>
			<InspectorControls>
				<ActiveCampaignOptions
					{...props}
					postId={postId}
				/>
			</InspectorControls>
			<ActiveCampaignEditor
				{...props}
			/>
		</>
	);
};
