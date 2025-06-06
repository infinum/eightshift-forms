import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { InputEditor } from './components/input-editor';
import { InputOptions } from './components/input-options';

export const Input = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={InputOptions}
			editor={InputEditor}
		/>
	);
};
