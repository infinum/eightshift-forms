import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { DynamicEditor } from './components/dynamic-editor';
import { DynamicOptions } from './components/dynamic-options';

export const Dynamic = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={DynamicOptions}
			editor={DynamicEditor}
		/>
	);
};
