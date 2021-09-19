import React from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, checkAttr } from '@eightshift/frontend-libs/scripts';
import { RadioEditor as RadioEditorComponent } from '../../../components/radio/components/radio-editor';
import manifest from './../manifest.json';

export const RadioEditor = ({ attributes, setAttributes }) => {
	const {
		blockClass,
	} = attributes;

	const radioAllowedBlocks = checkAttr('radioAllowedBlocks', attributes, manifest);

	return (
		<div className={blockClass}>
			<RadioEditorComponent
				{...props('radio', attributes, {
					setAttributes: setAttributes,
					radioContent: <InnerBlocks
														allowedBlocks={(typeof radioAllowedBlocks === 'undefined') || radioAllowedBlocks}
														templateLock={false}
													/>
				})}
			/>
		</div>
	);
}
