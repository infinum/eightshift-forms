import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldEditor } from './components/field-editor';
import { FieldOptions } from './components/field-options';

export const Field = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={FieldOptions}
			editor={FieldEditor}
		/>
	);
};
