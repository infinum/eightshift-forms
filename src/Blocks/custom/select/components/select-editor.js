import React from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, checkAttr } from '@eightshift/frontend-libs/scripts';
import { SelectEditor as SelectEditorComponent } from '../../../components/select/components/select-editor';
import manifest from './../manifest.json';

export const SelectEditor = ({ attributes, setAttributes, clientId }) => {
	const {
		template,
	} = manifest;

	const selectAllowedBlocks = checkAttr('selectAllowedBlocks', attributes, manifest);

	return (
		<SelectEditorComponent
			{...props('select', attributes, {
				setAttributes,
				clientId,
				selectContent: <InnerBlocks
												allowedBlocks={(typeof selectAllowedBlocks === 'undefined') || selectAllowedBlocks}
												template={template}
											/>
			})}
		/>
	);
};
