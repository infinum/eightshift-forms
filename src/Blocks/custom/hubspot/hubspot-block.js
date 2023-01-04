import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { select, dispatch } from "@wordpress/data";
import { createBlocksFromInnerBlocksTemplate } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { HubspotEditor } from './components/hubspot-editor';
import { HubspotOptions } from './components/hubspot-options';

export const Hubspot = (props) => {
	const {
		clientId,
	} = props;
	const postId = select('core/editor').getCurrentPostId();

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
				clientId={clientId}
			/>
		</>
	);
};
