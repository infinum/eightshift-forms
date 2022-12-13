import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { InnerBlocks } from '@wordpress/block-editor';
import apiFetch from '@wordpress/api-fetch';
import { props } from '@eightshift/frontend-libs/scripts';
import { buildOutputData, outputData } from './../../../assets/scripts/helpers/build-integration-form';

import { FormEditor } from '../../../components/form/components/form-editor';

export const HubspotEditor = ({ attributes, setAttributes, postId }) => {
	const {
		blockClass,
	} = attributes;

	const [formData, setFormData] = useState([]);

	// useEffect( () => {
	// 	apiFetch( { path: `eightshift-forms/v1/editor-form-builder/${postId}` } ).then( ( response ) => {
	// 		if (response.code === 200) {
	// 			setFormData(buildOutputData(response.data));
	// 		}
	// 	});
	// }, []);

	// console.log(outputData(formData));

	return (
		<div className={blockClass}>
			{/* <FormEditor
				{...props('form', attributes, {
					setAttributes,
					formContent: <InnerBlocks
													templateLock={'insert'}
													template={outputData(formData)}
												/>
				})}
			/> */}
		</div>
	);
};
