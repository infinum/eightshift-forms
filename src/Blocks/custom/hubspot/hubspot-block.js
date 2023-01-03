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

	// const [formData, setFormData] = useState([]);

	// useEffect( () => {
	// 	apiFetch( { path: `eightshift-forms/v1/integration-editor/${postId}` } ).then( ( response ) => {
	// 		if (response.code === 200) {

	// 			setFormData(createBlocksFromInnerBlocksTemplate(response.data.output));
	// 		}
	// 	});
	// }, []);

	// if (formData.length) {
	// 	console.log(formData, clientId);

	// 	// dispatch('core/block-editor').resetBlocks([]);
	// 	dispatch('core/block-editor').insertBlocks(
	// 		formData[0],
	// 		0,
	// 		clientId,
	// 	);
	// }

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
