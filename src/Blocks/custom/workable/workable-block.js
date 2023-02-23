import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { WorkableEditor } from './components/workable-editor';
import { WorkableOptions } from './components/workable-options';

export const Workable = (props) => {
	const itemIdKey = 'workableIntegrationId';

	return (
		<>
			<InspectorControls>
				<WorkableOptions
					{...props}
					clientId={props.clientId}
					itemIdKey={itemIdKey}
				/>
			</InspectorControls>
			<WorkableEditor
				{...props}
				itemIdKey={itemIdKey}
			/>
		</>
	);
};
