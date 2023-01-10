import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { GoodbitsEditor } from './components/goodbits-editor';
import { GoodbitsOptions } from './components/goodbits-options';

export const Goodbits = (props) => {
	const itemIdKey = 'goodbitsIntegrationId';

	return (
		<>
			<InspectorControls>
				<GoodbitsOptions
					{...props}
					clientId={props.clientId}
					itemIdKey={itemIdKey}
				/>
			</InspectorControls>
			<GoodbitsEditor
				{...props}
				itemIdKey={itemIdKey}
			/>
		</>
	);
};
