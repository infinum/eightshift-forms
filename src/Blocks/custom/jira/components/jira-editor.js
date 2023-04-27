import React from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, checkAttr, BlockInserter } from '@eightshift/frontend-libs/scripts';
import { FormEditor } from '../../../components/form/components/form-editor';
import manifest from '../manifest.json';

export const JiraEditor = ({ attributes, setAttributes, clientId }) => {
	const {
		blockClass,
	} = attributes;

	const jiraAllowedBlocks = checkAttr('jiraAllowedBlocks', attributes, manifest);

	return (
		<div className={blockClass}>
			<FormEditor
				{...props('form', attributes, {
					setAttributes,
					formContent: <InnerBlocks
						allowedBlocks={(typeof jiraAllowedBlocks === 'undefined') || jiraAllowedBlocks}
						templateLock={false}
						renderAppender={() => <BlockInserter clientId={clientId} />}
					/>
				})}
			/>
		</div>
	);
};
