import React from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, checkAttr } from '@eightshift/frontend-libs/scripts';
import { CheckboxEditor as CheckboxEditorComponent } from './../../../components/checkbox/components/checkbox-editor';
import manifest from './../manifest.json';


export const CheckboxEditor = ({ attributes, setAttributes }) => {
	const {
		blockClass,
	} = attributes;

	const checkboxAllowedBlocks = checkAttr('checkboxAllowedBlocks', attributes, manifest);

	return (
		<div className={blockClass}>
			<CheckboxEditorComponent
				{...props('checkbox', attributes, {
					setAttributes: setAttributes,
					checkboxContent: <InnerBlocks
														allowedBlocks={(typeof checkboxAllowedBlocks === 'undefined') || checkboxAllowedBlocks}
														templateLock={false}
													/>
				})}
			/>
		</div>
	);
}
