import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { HubspotEditor } from './components/hubspot-editor';
import { HubspotOptions } from './components/hubspot-options';

export const Hubspot = (props) => {
	return (
		<>
			<InspectorControls>
				<HubspotOptions {...props} />
			</InspectorControls>
			<HubspotEditor {...props} />
		</>
	);
};
