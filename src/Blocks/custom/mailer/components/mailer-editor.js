import React from 'react';
import { select } from '@wordpress/data';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, checkAttr, BlockInserter, STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { FormEditor } from '../../../components/form/components/form-editor';

export const MailerEditor = ({ attributes, setAttributes, clientId }) => {
	const manifest = select(STORE_NAME).getBlock('mailer');

	const {
		blockClass,
	} = attributes;

	const mailerAllowedBlocks = checkAttr('mailerAllowedBlocks', attributes, manifest);

	return (
		<div className={blockClass}>
			<FormEditor
				{...props('form', attributes, {
					setAttributes,
					formContent:
						<InnerBlocks
							allowedBlocks={(typeof mailerAllowedBlocks === 'undefined') || mailerAllowedBlocks}
							templateLock={false}
							renderAppender={() => <BlockInserter clientId={clientId} />}
						/>
				})}
			/>
		</div>
	);
};
