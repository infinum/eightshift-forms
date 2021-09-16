import React from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, checkAttr } from '@eightshift/frontend-libs/scripts';
import { FieldsetEditor } from '../../../components/fieldset/components/fieldset-editor';
import manifest from './../manifest.json';


export const CheckboxEditor = ({ attributes, setAttributes }) => {
	const {
		blockClass,
	} = attributes;

	const checkboxAllowedBlocks = checkAttr('checkboxAllowedBlocks', attributes, manifest);

	return (
		<div className={blockClass}>
			<FieldsetEditor
				{...props('fieldset', attributes, {
					setAttributes: setAttributes,
					fieldsetContent: <InnerBlocks
														allowedBlocks={(typeof checkboxAllowedBlocks === 'undefined') || checkboxAllowedBlocks}
														templateLock={false}
													/>
				})}
			/>
		</div>
	);
}
