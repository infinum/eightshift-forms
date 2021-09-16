import React from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, checkAttr } from '@eightshift/frontend-libs/scripts';
import { SelectEditor as SelectEditorComponent } from '../../../components/select/components/select-editor';
import manifest from './../manifest.json';

export const SelectEditor = ({ attributes, setAttributes }) => {

	const {
		blockClass,
	} = attributes;

	const selectAllowedBlocks = checkAttr('selectAllowedBlocks', attributes, manifest);

	return (
		<div className={blockClass}>
			<SelectEditorComponent
				{...props('select', attributes, {
					setAttributes: setAttributes,
					selectOptions: <InnerBlocks
														allowedBlocks={(typeof selectAllowedBlocks === 'undefined') || selectAllowedBlocks}
														templateLock={false}
													/>
				})}
			/>
		</div>
	);
}
