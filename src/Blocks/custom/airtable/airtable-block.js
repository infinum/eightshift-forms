import React from 'react';
import { useSelect } from "@wordpress/data";
import { InspectorControls } from '@wordpress/block-editor';
import { AirtableEditor } from './components/airtable-editor';
import { AirtableOptions } from './components/airtable-options';

export const Airtable = (props) => {
	const postId = useSelect((select) => select('core/editor').getCurrentPostId());

	return (
		<>
			<InspectorControls>
				<AirtableOptions
					{...props}
					postId={postId}
				/>
			</InspectorControls>
			<AirtableEditor
				{...props}
				postId={postId}
			/>
		</>
	);
};
