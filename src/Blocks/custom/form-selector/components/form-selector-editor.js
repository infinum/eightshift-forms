import React from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { checkAttr } from '@eightshift/frontend-libs/scripts';
import manifest from './../manifest.json';

export const FormSelectorEditor = ({ attributes }) => {

	const formSelectorAllowedBlocks = checkAttr('formSelectorAllowedBlocks', attributes, manifest);

	return (
		<InnerBlocks
			allowedBlocks={(typeof formSelectorAllowedBlocks === 'undefined') || formSelectorAllowedBlocks}
			templateLock={false}
		/>
	);
}
