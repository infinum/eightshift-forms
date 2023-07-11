import React from 'react';
import { select } from '@wordpress/data';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, checkAttr, BlockInserter, STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { SelectEditor as SelectEditorComponent } from '../../../components/select/components/select-editor';

export const SelectEditor = ({ attributes, setAttributes, clientId }) => {
	const manifest = select(STORE_NAME).getBlock('select');

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
					renderAppender={() => <BlockInserter clientId={clientId} small />}
				/>
			})}
		/>
	);
};
