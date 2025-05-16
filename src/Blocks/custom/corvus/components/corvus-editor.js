import React from 'react';
import { select } from '@wordpress/data';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, BlockInserter, STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { FormEditor, additionalBlocksNoIntegration } from '../../../components/form/components/form-editor';

export const CorvusEditor = ({ attributes, setAttributes, clientId }) => {
	const manifest = select(STORE_NAME).getBlock('corvus');

	const {
		blockClass,
	} = attributes;

	return (
		<div className={blockClass}>
			<FormEditor
				{...props('form', attributes, {
					setAttributes,
					formContent: <InnerBlocks
						allowedBlocks={additionalBlocksNoIntegration}
						renderAppender={() => <BlockInserter clientId={clientId} />}
					/>
				})}
			/>
		</div>
	);
};
