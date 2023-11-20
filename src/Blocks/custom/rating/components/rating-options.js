import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { RatingOptions as RatingOptionsComponent } from '../../../components/rating/components/rating-options';

export const RatingOptions = ({ attributes, setAttributes }) => {
	return (
		<RatingOptionsComponent
			{...props('rating', attributes, {
				setAttributes,
			})}
		/>
	);
};
