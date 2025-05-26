import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { DateEditor } from './components/date-editor';
import { DateOptions } from './components/date-options';

export const Date = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={DateOptions}
			editor={DateEditor}
		/>
	);
};
