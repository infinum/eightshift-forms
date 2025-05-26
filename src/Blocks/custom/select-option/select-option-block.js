import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { SelectOptionEditor } from './components/select-option-editor';
import { SelectOptionOptions } from './components/select-option-options';

export const SelectOption = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={SelectOptionOptions}
			editor={SelectOptionEditor}
		/>
	);
};
