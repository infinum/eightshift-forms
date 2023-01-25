import React from 'react';
import { InnerBlocks } from '@wordpress/block-editor';
import { props, checkAttr } from '@eightshift/frontend-libs/scripts';
import { CountryEditor as CountryEditorComponent } from '../../../components/country/components/country-editor';
import manifest from '../manifest.json';

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
