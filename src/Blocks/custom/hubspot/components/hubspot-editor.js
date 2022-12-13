import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { InnerBlocks } from '@wordpress/block-editor';
import apiFetch from '@wordpress/api-fetch';
import { props, checkAttr } from '@eightshift/frontend-libs/scripts';
import { buildOutputData, outputData } from './../../../assets/scripts/helpers/build-integration-form';
import manifest from '../manifest.json';
import { FormEditor } from '../../../components/form/components/form-editor';

export const HubspotEditor = ({ attributes, setAttributes, postId }) => {
	const {
		blockClass,
	} = attributes;

	const [formData, setFormData] = useState([]);

	const hubspotIntegrationId = checkAttr('hubspotIntegrationId', attributes, manifest);

	// useEffect( () => {
	// 	apiFetch( { path: `eightshift-forms/v1/integration-editor-hubspot/?id=${postId}&itemId=${hubspotIntegrationId}` } ).then( ( response ) => {
	// 		if (response.code === 200) {
	// 			setFormData(response.data);
	// 		}
	// 	});
	// }, []);

	console.log(formData);
	

	return (
		<div className={blockClass}>
			<FormEditor
				{...props('form', attributes, {
					setAttributes,
					formContent: <InnerBlocks
													templateLock={'insert'}
													template={(formData)}
												/>
				})}
			/>
		</div>
	);
};
