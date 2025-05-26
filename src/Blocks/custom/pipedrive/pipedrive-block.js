import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { PipedriveEditor } from './components/pipedrive-editor';
import { PipedriveOptions } from './components/pipedrive-options';

export const Pipedrive = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={PipedriveOptions}
			editor={PipedriveEditor}
		/>
	);
};
