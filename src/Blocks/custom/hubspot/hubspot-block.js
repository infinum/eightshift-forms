import React from 'react';
import { useSelect } from "@wordpress/data";
import { InspectorControls } from '@wordpress/block-editor';
import { HubspotEditor } from './components/hubspot-editor';
import { HubspotOptions } from './components/hubspot-options';

export const Hubspot = (props) => {
	const postId = useSelect((select) => select('core/editor').getCurrentPostId());

	return (
		<>
			<InspectorControls>
				<HubspotOptions
					{...props}
					postId={postId}
				/>
			</InspectorControls>
			<HubspotEditor
				{...props}
				postId={postId}
			/>
		</>
	);
};
