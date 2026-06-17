import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { PardotEditor } from './components/pardot-editor';
import { PardotOptions } from './components/pardot-options';

export const Pardot = (props) => {
	const itemIdKey = 'pardotIntegrationId';

	return (
		<>
			<InspectorControls>
				<PardotOptions
					{...props}
					clientId={props.clientId}
					itemIdKey={itemIdKey}
				/>
			</InspectorControls>
			<PardotEditor
				{...props}
				itemIdKey={itemIdKey}
			/>
		</>
	);
};
