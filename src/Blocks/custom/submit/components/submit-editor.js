import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { SubmitEditor as SubmitEditorComponent } from '../../../components/submit/components/submit-editor';

export const SubmitEditor = ({ attributes, setAttributes }) => {

	const {
		blockClass,
	} = attributes;

	return (
		<SubmitEditorComponent
			{...props('submit', attributes, {
				setAttributes: setAttributes,
				blockClass,
			})}
		/>
	);
}
