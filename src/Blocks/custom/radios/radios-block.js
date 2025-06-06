import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { RadiosEditor } from './components/radios-editor';
import { RadiosOptions } from './components/radios-options';

export const Radios = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={RadiosOptions}
			editor={RadiosEditor}
		/>
	);
};
