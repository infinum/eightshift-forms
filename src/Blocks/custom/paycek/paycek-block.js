import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { PaycekEditor } from './components/paycek-editor';
import { PaycekOptions } from './components/paycek-options';

export const Paycek = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={PaycekOptions}
			editor={PaycekEditor}
		/>
	);
};
