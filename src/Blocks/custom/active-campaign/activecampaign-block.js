import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { ActiveCampaignEditor } from './components/activecampaign-editor';
import { ActiveCampaignOptions } from './components/activecampaign-options';

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
