import React from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { props } from '@eightshift/frontend-libs/scripts';
import { FormEditor } from '../../../components/form/components/form-editor';

export const HubspotEditor = ({ attributes, setAttributes }) => {
	const {
		blockClass,
	} = attributes;

	return (
		<div className={blockClass}>
			<FormEditor
				{...props('form', attributes, {
					setAttributes,
					formContent: <InnerBlocks
													// templateLock={'none'}
													// template={
													// 	formData.length ? [
													// 	formData[0].name, formData[0].attributes, []
													// ] : []}
												/>
				})}
			/>
		</div>
	);
};
