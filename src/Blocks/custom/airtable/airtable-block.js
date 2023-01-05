import React from 'react';
import { select } from "@wordpress/data";
import { InspectorControls } from '@wordpress/block-editor';
import { AirtableEditor } from './components/airtable-editor';
import { AirtableOptions } from './components/airtable-options';

export const Airtable = (props) => {
	const postId = select('core/editor').getCurrentPostId();

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
			/>
		</>
	);
};
