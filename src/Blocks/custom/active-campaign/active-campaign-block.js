import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { ActiveCampaignEditor } from './components/active-campaign-editor';
import { ActiveCampaignOptions } from './components/active-campaign-options';

export const ActiveCampaign = (props) => {
	const itemIdKey = 'activeCampaignIntegrationId';

	return (
		<>
			<InspectorControls>
				<ActiveCampaignOptions
					{...props}
					clientId={props.clientId}
					itemIdKey={itemIdKey}
				/>
			</InspectorControls>
			<ActiveCampaignEditor
				{...props}
				itemIdKey={itemIdKey}
			/>
		</>
	);
};
