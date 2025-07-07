import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { FileEditor } from './components/file-editor';
import { FileOptions } from './components/file-options';

export const File = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={FileOptions}
			editor={FileEditor}
		/>
	);
};
