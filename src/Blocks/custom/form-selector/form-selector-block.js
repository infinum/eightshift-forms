import React from 'react';
import { useSelect, select } from '@wordpress/data';
import { InspectorControls } from '@wordpress/block-editor';
import { FormSelectorEditor } from './components/form-selector-editor';
import { FormSelectorOptions } from './components/form-selector-options';

export const FormSelector = (props) => {
	const {
		clientId
	} = props;

	const postId = select('core/editor').getCurrentPostId();

	// Check if form selector has inner blocks.
	const hasInnerBlocks = useSelect((select) => {
		const blocks = select('core/block-editor').getBlock(clientId);

		return blocks?.innerBlocks.length !== 0;
	});

	return (
		<>
			<InspectorControls>
				<FormSelectorOptions
					{...props}
					clientId={clientId}
					postId={postId}
					hasInnerBlocks={hasInnerBlocks}
				/>
			</InspectorControls>
			<FormSelectorEditor
				{...props}
				clientId={clientId}
				hasInnerBlocks={hasInnerBlocks}
			/>
		</>
	);
};
