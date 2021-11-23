import React from 'react';
import { useSelect } from "@wordpress/data";
import { InspectorControls } from '@wordpress/block-editor';
import { GoodbitsEditor } from './components/goodbits-editor';
import { GoodbitsOptions } from './components/goodbits-options';

export const Goodbits = (props) => {
	const postId = useSelect((select) => select('core/editor').getCurrentPostId());

	return (
		<>
			<InspectorControls>
				<GoodbitsOptions
					{...props}
					postId={postId}
				/>
			</InspectorControls>
			<GoodbitsEditor
				{...props}
				postId={postId}
			/>
		</>
	);
};
