import React from 'react';
import { select } from '@wordpress/data';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, checkAttr, BlockInserter, STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { CheckboxesEditor as CheckboxesEditorComponent } from '../../../components/checkboxes/components/checkboxes-editor';

export const CheckboxesEditor = ({ attributes, setAttributes, clientId }) => {
	const manifest = select(STORE_NAME).getBlock('checkboxes');

	const {
		template,
	} = manifest;

	const {
		blockClass,
	} = attributes;

	const checkboxesAllowedBlocks = checkAttr('checkboxesAllowedBlocks', attributes, manifest);

	return (
		<CheckboxesEditorComponent
			{...props('checkboxes', attributes, {
				setAttributes,
				blockClass,
				clientId,
				checkboxesContent:
					<InnerBlocks
						allowedBlocks={(typeof checkboxesAllowedBlocks === 'undefined') || checkboxesAllowedBlocks}
						template={template}
						renderAppender={() => <BlockInserter clientId={clientId} small />}
					/>
			})}
		/>
	);
};
