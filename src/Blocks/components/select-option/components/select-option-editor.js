import React from 'react';
import { __ } from '@wordpress/i18n';
import { checkAttr } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const SelectOptionEditor = (attributes) => {

	const selectOptionLabel = checkAttr('selectOptionLabel', attributes, manifest);

	return (
		<>
			{selectOptionLabel ? selectOptionLabel : __('Enter option detail in sidebar', 'eightshift-forms')}
		</>
	);
};
