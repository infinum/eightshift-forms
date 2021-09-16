import React from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, checkAttr } from '@eightshift/frontend-libs/scripts';
import { FieldsetEditor } from '../../../components/fieldset/components/fieldset-editor';
import manifest from './../manifest.json';

export const RadioEditor = ({ attributes, setAttributes }) => {
	const {
		blockClass,
	} = attributes;

	const radioAllowedBlocks = checkAttr('radioAllowedBlocks', attributes, manifest);

	return (
		<div className={blockClass}>
			<FieldsetEditor
				{...props('fieldset', attributes, {
					setAttributes: setAttributes,
					fieldsetContent: <InnerBlocks
														allowedBlocks={(typeof radioAllowedBlocks === 'undefined') || radioAllowedBlocks}
														templateLock={false}
													/>
				})}
			/>
		</div>
	);
}
