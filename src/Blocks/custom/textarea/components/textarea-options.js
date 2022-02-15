import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { TextareaOptions as TextareaOptionsComponent } from '../../../components/textarea/components/textarea-options';

export const TextareaOptions = ({ attributes, setAttributes }) => {
	return (
		<TextareaOptionsComponent
			{...props('textarea', attributes, {
				setAttributes,
			})}
		/>
	);
};
