import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { SubmitEditor } from './components/submit-editor';
import { SubmitOptions } from './components/submit-options';

export const Submit = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={SubmitOptions}
			editor={SubmitEditor}
		/>
	);
};
