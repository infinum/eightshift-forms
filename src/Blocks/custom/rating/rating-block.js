import React from 'react';
import { GutenbergBlock } from '@eightshift/frontend-libs-tailwind/scripts';
import { RatingEditor } from './components/rating-editor';
import { RatingOptions } from './components/rating-options';

export const Rating = (props) => {
	return (
		<GutenbergBlock
			{...props}
			options={RatingOptions}
			editor={RatingEditor}
		/>
	);
};
