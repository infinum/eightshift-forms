import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { MomentsEditor } from './components/moments-editor';
import { MomentsOptions } from './components/moments-options';

export const Moments = (props) => {
	const itemIdKey = 'momentsIntegrationId';

	return (
		<>
			<InspectorControls>
				<MomentsOptions
					{...props}
					clientId={props.clientId}
					itemIdKey={itemIdKey}
				/>
			</InspectorControls>
			<MomentsEditor
				{...props}
				itemIdKey={itemIdKey}
			/>
		</>
	);
};
