import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { CountryEditor } from './components/country-editor';
import { CountryOptions } from './components/country-options';

export const Country = (props) => {
	return (
		<>
			<InspectorControls>
				<CountryOptions {...props} />
			</InspectorControls>
			<CountryEditor {...props} />
		</>
	);
};
