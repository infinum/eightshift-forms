import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { DateEditor as DateEditorComponent } from '../../../components/date/components/date-editor';

export const DateEditor = ({ attributes, setAttributes, clientId }) => {
	return (
		<DateEditorComponent
			{...props('date', attributes, {
				setAttributes,
				clientId,
			})}
		/>
	);
};
