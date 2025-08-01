import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { ActivecampaignEditor } from './components/activecampaign-editor';
import { ActivecampaignOptions } from './components/activecampaign-options';

export const Activecampaign = (props) => {
	const itemIdKey = 'activeCampaignIntegrationId';

	return (
		<>
			<InspectorControls>
				<ActivecampaignOptions
					{...props}
					clientId={props.clientId}
					itemIdKey={itemIdKey}
				/>
			</InspectorControls>
			<ActivecampaignEditor
				{...props}
				itemIdKey={itemIdKey}
			/>
		</>
	);
};
