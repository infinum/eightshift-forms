import React from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, checkAttr } from '@eightshift/frontend-libs/scripts';
import { CheckboxesEditor as CheckboxesEditorComponent } from '../../../components/checkboxes/components/checkboxes-editor';
import manifest from '../manifest.json';


export const CheckboxesEditor = ({ attributes, setAttributes }) => {
	const {
		blockClass,
	} = attributes;

	const checkboxesAllowedBlocks = checkAttr('checkboxesAllowedBlocks', attributes, manifest);

	return (
		<CheckboxesEditorComponent
			{...props('checkboxes', attributes, {
				setAttributes: setAttributes,
				blockClass,
				checkboxesContent: <InnerBlocks
													allowedBlocks={(typeof checkboxesAllowedBlocks === 'undefined') || checkboxesAllowedBlocks}
													templateLock={false}
												/>
			})}
		/>
	);
}
