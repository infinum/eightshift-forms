import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { StepEditor } from './components/step-editor';
import { StepOptions } from './components/step-options';

export const Step = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={StepOptions}
			editor={StepEditor}
		/>
	);
};
