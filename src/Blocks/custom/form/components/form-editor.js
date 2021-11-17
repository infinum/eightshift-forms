import React from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, checkAttr } from '@eightshift/frontend-libs/scripts';
import { FormEditor as FormEditorComponent } from '../../../components/form/components/form-editor';
import manifest from './../manifest.json';

export const FormEditor = ({ attributes, setAttributes }) => {

	const {
		blockClass,
	} = attributes;

	const formAllowedBlocks = checkAttr('formAllowedBlocks', attributes, manifest);

	return (
		<div className={blockClass}>
			<FormEditorComponent
				{...props('form', attributes, {
					setAttributes,
					formContent: <InnerBlocks
													allowedBlocks={(typeof formAllowedBlocks === 'undefined') || formAllowedBlocks}
													templateLock={false}
												/>
				})}
			/>
		</div>
	);
};
