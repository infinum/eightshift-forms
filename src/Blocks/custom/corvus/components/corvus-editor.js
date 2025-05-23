import React from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, BlockInserter } from '@eightshift/frontend-libs-tailwind/scripts';
import { FormEditor, additionalBlocksNoIntegration } from '../../../components/form/components/form-editor';

export const CorvusEditor = ({ attributes, setAttributes, clientId }) => {
	const { blockClass } = attributes;

	return (
		<div className={blockClass}>
			<FormEditor
				{...props('form', attributes, {
					setAttributes,
					formContent: (
						<InnerBlocks
							allowedBlocks={additionalBlocksNoIntegration}
							renderAppender={() => <BlockInserter clientId={clientId} />}
						/>
					),
				})}
			/>
		</div>
	);
};
