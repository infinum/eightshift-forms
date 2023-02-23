import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { GreenhouseEditor } from './components/greenhouse-editor';
import { GreenhouseOptions } from './components/greenhouse-options';

export const Greenhouse = (props) => {
	const itemIdKey = 'greenhouseIntegrationId';

	return (
		<>
			<InspectorControls>
				<GreenhouseOptions
					{...props}
					clientId={props.clientId}
					itemIdKey={itemIdKey}
				/>
			</InspectorControls>
			<GreenhouseEditor
				{...props}
				itemIdKey={itemIdKey}
			/>
		</>
	);
};
