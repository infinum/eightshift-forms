import React from 'react';
import { __ } from '@wordpress/i18n';
import classnames from 'classnames';
import { selector, checkAttr } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const SelectOptionEditor = (attributes) => {
	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		blockClass,
		additionalClass,
	} = attributes;

	const selectOptionLabel = checkAttr('selectOptionLabel', attributes, manifest);

	const selectOptionClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
		selector(selectOptionLabel === '', componentClass, '', 'placeholder'),
	]);

	return (
		<div className={selectOptionClass}>
			{selectOptionLabel ? selectOptionLabel : __('Enter option label in sidebar.', 'eightshift-forms')}
		</div>
	);
};
