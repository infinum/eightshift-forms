import React from 'react';
import { select } from "@wordpress/data";
import { InspectorControls } from '@wordpress/block-editor';
import { GoodbitsEditor } from './components/goodbits-editor';
import { GoodbitsOptions } from './components/goodbits-options';

export const Goodbits = (props) => {
	const postId = select('core/editor').getCurrentPostId();

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
			/>
		</>
	);
};
