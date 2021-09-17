import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { FileEditor } from './components/file-editor';
import { FileOptions } from './components/file-options';

export const File = (props) => {
	return (
		<>
			<InspectorControls>
				<FileOptions {...props} />
			</InspectorControls>
			<FileEditor {...props} />
		</>
	);
};
