import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { PhoneEditor } from './components/phone-editor';
import { PhoneOptions } from './components/phone-options';

export const Phone = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={PhoneOptions}
			editor={PhoneEditor}
		/>
	);
};
