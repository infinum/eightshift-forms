import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { CheckboxesEditor } from './components/checkboxes-editor';
import { CheckboxesOptions } from './components/checkboxes-options';

export const Checkboxes = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={CheckboxesOptions}
			editor={CheckboxesEditor}
		/>
	);
};
