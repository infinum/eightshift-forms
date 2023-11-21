import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { RatingEditor } from './components/rating-editor';
import { RatingOptions } from './components/rating-options';

export const Rating = (props) => {
	return (
		<>
			<InspectorControls>
				<RatingOptions {...props} />
			</InspectorControls>
			<RatingEditor {...props} />
		</>
	);
};
