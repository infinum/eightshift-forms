import React from 'react';
import { useSelect } from "@wordpress/data";
import { InspectorControls } from '@wordpress/block-editor';
import { WorkableEditor } from './components/workable-editor';
import { WorkableOptions } from './components/workable-options';

export const Workable = (props) => {
	const postId = useSelect((select) => select('core/editor').getCurrentPostId());

	return (
		<>
			<InspectorControls>
				<WorkableOptions
					{...props}
					postId={postId}
				/>
			</InspectorControls>
			<WorkableEditor
				{...props}
				postId={postId}
			/>
		</>
	);
};
