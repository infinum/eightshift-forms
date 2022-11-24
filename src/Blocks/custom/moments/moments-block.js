import React from 'react';
import { useSelect } from "@wordpress/data";
import { InspectorControls } from '@wordpress/block-editor';
import { MomentsEditor } from './components/moments-editor';
import { MomentsOptions } from './components/moments-options';

export const Moments = (props) => {
	const postId = useSelect((select) => select('core/editor').getCurrentPostId());

	return (
		<>
			<InspectorControls>
				<MomentsOptions
					{...props}
					postId={postId}
				/>
			</InspectorControls>
			<MomentsEditor
				{...props}
				postId={postId}
			/>
		</>
	);
};
