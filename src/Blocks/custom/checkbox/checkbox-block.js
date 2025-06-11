import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { CheckboxEditor } from './components/checkbox-editor';
import { CheckboxOptions } from './components/checkbox-options';

export const Checkbox = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={CheckboxOptions}
			editor={CheckboxEditor}
		/>
	);
};
