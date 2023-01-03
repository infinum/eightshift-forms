import React, { useEffect } from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, checkAttr } from '@eightshift/frontend-libs/scripts';

import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { select, dispatch } from "@wordpress/data";
import { createBlocksFromInnerBlocksTemplate, createBlock } from '@wordpress/blocks';

import { buildOutputData, outputData } from './../../../assets/scripts/helpers/build-integration-form';
import manifest from '../manifest.json';
import { FormEditor } from '../../../components/form/components/form-editor';

export const HubspotEditor = ({ attributes, setAttributes, postId, clientId }) => {
	const {
		blockClass,
	} = attributes;

	// const {
	// 	clientId,
	// } = props;
	// const postId = select('core/editor').getCurrentPostId();

	const [formData, setFormData] = useState([]);
	const [isLoaded, setIsLoaded] = useState(false);

	const a = apiFetch( { path: `eightshift-forms/v1/integration-editor/${postId}` } ).then( ( response ) => {
		if (response.code === 200) {
			return response.data.output;
		}

		return [];
	});

	useEffect( () => {
		setFormData(a);
	}, formData);

	if (!isLoaded) {
		// dispatch('core/block-editor').resetBlocks([]);

		console.log('laded')
		console.log(formData);
		
		

		if (formData.length) {
			const blocks = createBlocksFromInnerBlocksTemplate(formData);
			console.log(blocks);
			
				// dispatch('core/block-editor').resetBlocks(blocks);
				// dispatch('core/block-editor').insertBlocks(
				// 	blocks,
				// 	0,
				// 	clientId
				// );
			}
			setIsLoaded(true);
		}

	return (
		<div className={blockClass}>
			<FormEditor
				{...props('form', attributes, {
					setAttributes,
					formContent: <InnerBlocks
													// templateLock={'none'}
													// template={
													// 	formData.length ? [
													// 	formData[0].name, formData[0].attributes, []
													// ] : []}
												/>
				})}
			/>
		</div>
	);
};
