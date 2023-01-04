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
