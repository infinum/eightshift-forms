import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { CountryEditor as CountryEditorComponent } from '../../../components/country/components/country-editor';

export const CountryEditor = ({ attributes, setAttributes, clientId }) => {
	return (
		<CountryEditorComponent
			{...props('country', attributes, {
				setAttributes,
				clientId,
			})}
		/>
	);
};
