import React from 'react';
import { useSelect } from "@wordpress/data";
import { InspectorControls } from '@wordpress/block-editor';
import { ActiveCampaignEditor } from './components/active-campaign-editor';
import { ActiveCampaignOptions } from './components/active-campaign-options';

export const ActiveCampaign = (props) => {
	const postId = useSelect((select) => select('core/editor').getCurrentPostId());

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
				postId={postId}
			/>
		</>
	);
};
