import React from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, checkAttr } from '@eightshift/frontend-libs/scripts';
import { FormEditor } from '../../../components/form/components/form-editor';
import manifest from '../manifest.json';

export const JiraEditor = ({ attributes, setAttributes }) => {
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
												/>
				})}
			/>
		</div>
	);
};
