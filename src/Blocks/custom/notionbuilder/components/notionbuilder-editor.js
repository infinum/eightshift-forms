import React from 'react';
import { select } from '@wordpress/data';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, checkAttr, BlockInserter, STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { FormEditor } from '../../../components/form/components/form-editor';

export const NotionbuilderEditor = ({ attributes, setAttributes, clientId }) => {
	const manifest = select(STORE_NAME).getBlock('notionbuilder');

	const {
		blockClass,
	} = attributes;

	const notionbuilderAllowedBlocks = checkAttr('notionbuilderAllowedBlocks', attributes, manifest);

	return (
		<div className={blockClass}>
			<FormEditor
				{...props('form', attributes, {
					setAttributes,
					formContent: <InnerBlocks
						allowedBlocks={(typeof notionbuilderAllowedBlocks === 'undefined') || notionbuilderAllowedBlocks}
						templateLock={false}
						renderAppender={() => <BlockInserter clientId={clientId} />}
					/>
				})}
			/>
		</div>
	);
};
