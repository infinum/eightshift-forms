import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { AirtableEditor } from './components/airtable-editor';
import { AirtableOptions } from './components/airtable-options';

export const Airtable = (props) => {
	const itemIdKey = 'airtableIntegrationId';
	const innerIdKey = 'airtableIntegrationInnerId';

	return (
		<>
			<InspectorControls>
				<AirtableOptions
					{...props}
					clientId={props.clientId}
					itemIdKey={itemIdKey}
					innerIdKey={innerIdKey}
				/>
			</InspectorControls>
			<AirtableEditor
				{...props}
				itemIdKey={itemIdKey}
				innerIdKey={innerIdKey}
			/>
		</>
	);
};
