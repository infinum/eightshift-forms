import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { TextareaEditor } from './components/textarea-editor';
import { TextareaOptions } from './components/textarea-options';

export const Textarea = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={TextareaOptions}
			editor={TextareaEditor}
		/>
	);
};
