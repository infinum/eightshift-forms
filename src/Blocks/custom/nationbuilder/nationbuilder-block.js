import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { NationbuilderEditor } from './components/nationbuilder-editor';
import { NationbuilderOptions } from './components/nationbuilder-options';

export const Nationbuilder = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={NationbuilderOptions}
			editor={NationbuilderEditor}
		/>
	);
};
