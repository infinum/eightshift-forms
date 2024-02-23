import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { DynamicEditor as DynamicEditorComponent } from '../../../components/dynamic/components/dynamic-editor';

export const DynamicEditor = ({ attributes, setAttributes, clientId }) => {
	return (
		<DynamicEditorComponent
			{...props('dynamic', attributes, {
				setAttributes,
				clientId,
			})}
		/>
	);
};
