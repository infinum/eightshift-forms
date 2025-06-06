import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { RadioEditor } from './components/radio-editor';
import { RadioOptions } from './components/radio-options';

export const Radio = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={RadioOptions}
			editor={RadioEditor}
		/>
	);
};
