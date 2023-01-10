import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { HubspotEditor } from './components/hubspot-editor';
import { HubspotOptions } from './components/hubspot-options';

export const Hubspot = (props) => {
	const itemIdKey = 'hubspotIntegrationId';

	return (
		<>
			<InspectorControls>
				<HubspotOptions
					{...props}
					clientId={props.clientId}
					itemIdKey={itemIdKey}
				/>
			</InspectorControls>
			<HubspotEditor
				{...props}
				itemIdKey={itemIdKey}
			/>
		</>
	);
};
