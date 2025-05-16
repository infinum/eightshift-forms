/* global esFormsLocalization */

import React from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, BlockInserter } from '@eightshift/frontend-libs/scripts';
import { additionalBlocksNoIntegration, FormEditor } from '../../../components/form/components/form-editor';

export const MailerEditor = ({ attributes, setAttributes, clientId }) => {
	const {
		blockClass,
	} = attributes;

	return (
		<div className={blockClass}>
			<FormEditor
				{...props('form', attributes, {
					setAttributes,
					formContent:
						<InnerBlocks
							allowedBlocks={additionalBlocksNoIntegration}
							renderAppender={() => <BlockInserter clientId={clientId} />}
						/>
				})}
			/>
		</div>
	);
};
