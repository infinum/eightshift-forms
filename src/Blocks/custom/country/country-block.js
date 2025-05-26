import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { CountryEditor } from './components/country-editor';
import { CountryOptions } from './components/country-options';

export const Country = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={CountryOptions}
			editor={CountryEditor}
		/>
	);
};
