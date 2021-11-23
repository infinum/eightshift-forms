import React from 'react';
import { useSelect } from "@wordpress/data";
import { InspectorControls } from '@wordpress/block-editor';
import { GreenhouseEditor } from './components/greenhouse-editor';
import { GreenhouseOptions } from './components/greenhouse-options';

export const Greenhouse = (props) => {
	const postId = useSelect((select) => select('core/editor').getCurrentPostId());

	return (
		<>
			<InspectorControls>
				<GreenhouseOptions
					{...props}
					postId={postId}
				/>
			</InspectorControls>
			<GreenhouseEditor
				{...props}
				postId={postId}
			/>
		</>
	);
};
