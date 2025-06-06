import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { ResultOutputItemEditor } from './components/result-output-item-editor';
import { ResultOutputItemOptions } from './components/result-output-item-options';

export const ResultOutputItem = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={ResultOutputItemOptions}
			editor={ResultOutputItemEditor}
		/>
	);
};
