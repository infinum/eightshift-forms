import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { RatingEditor as RatingEditorComponent } from '../../../components/rating/components/rating-editor';

export const RatingEditor = ({ attributes, setAttributes, clientId }) => {
	return (
		<RatingEditorComponent
			{...props('rating', attributes, {
				setAttributes,
				clientId,
			})}
		/>
	);
};
