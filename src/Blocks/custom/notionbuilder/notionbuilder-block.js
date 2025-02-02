import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { NotionbuilderEditor } from './components/notionbuilder-editor';
import { NotionbuilderOptions } from './components/notionbuilder-options';

export const Notionbuilder = (props) => {
	return (
		<>
			<InspectorControls>
				<NotionbuilderOptions {...props} />
			</InspectorControls>
			<NotionbuilderEditor {...props} />
		</>
	);
};
