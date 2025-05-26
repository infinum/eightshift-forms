import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { SelectEditor } from './components/select-editor';
import { SelectOptions } from './components/select-options';

export const Select = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={SelectOptions}
			editor={SelectEditor}
		/>
	);
};
