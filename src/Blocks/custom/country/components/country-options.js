import React from 'react';
import { props } from '@eightshift/frontend-libs/scripts';
import { CountryOptions as CountryOptionsComponent } from '../../../components/country/components/country-options';

export const CountryOptions = ({ attributes, setAttributes }) => {
	return (
		<CountryOptionsComponent
			{...props('country', attributes, {
				setAttributes,
			})}
		/>
	);
};
