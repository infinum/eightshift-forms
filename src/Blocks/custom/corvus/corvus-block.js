import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { CorvusEditor } from './components/corvus-editor';
import { CorvusOptions } from './components/corvus-options';

export const Corvus = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={CorvusOptions}
			editor={CorvusEditor}
		/>
	);
};
