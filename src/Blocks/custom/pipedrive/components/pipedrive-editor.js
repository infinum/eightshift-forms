import React from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, BlockInserter } from '@eightshift/frontend-libs/scripts';
import { FormEditor, additionalBlocksNoIntegration } from '../../../components/form/components/form-editor';

export const PipedriveEditor = ({ attributes, setAttributes, clientId }) => {
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
