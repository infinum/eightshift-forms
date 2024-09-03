import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { TalentlyftEditor } from './components/talentlyft-editor';
import { TalentlyftOptions } from './components/talentlyft-options';

export const Talentlyft = (props) => {
	const itemIdKey = 'talentlyftIntegrationId';

	return (
		<>
			<InspectorControls>
				<TalentlyftOptions
					{...props}
					clientId={props.clientId}
					itemIdKey={itemIdKey}
				/>
			</InspectorControls>
			<TalentlyftEditor
				{...props}
				itemIdKey={itemIdKey}
			/>
		</>
	);
};
