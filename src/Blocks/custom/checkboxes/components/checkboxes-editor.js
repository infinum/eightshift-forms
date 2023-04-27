import React from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, checkAttr, BlockInserter } from '@eightshift/frontend-libs/scripts';
import { CheckboxesEditor as CheckboxesEditorComponent } from '../../../components/checkboxes/components/checkboxes-editor';
import manifest from '../manifest.json';

export const CheckboxesEditor = ({ attributes, setAttributes, clientId }) => {
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
