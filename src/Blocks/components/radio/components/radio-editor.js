import React from 'react';
import { __ } from '@wordpress/i18n';
import classnames from 'classnames';
import { selector, checkAttr } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const RadioEditor = (attributes) => {
	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		blockClass,
		additionalClass,
	} = attributes;

	const radioLabel = checkAttr('radioLabel', attributes, manifest);

	const radioClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	]);

	const radioLabelClass = classnames([
		selector(componentClass, componentClass, 'label'),
		selector(radioLabel === '', componentClass, 'label', 'placeholder'),
	]);

	return (
		<div className={radioClass}>
			<div className={`${componentClass}__content`}>
				<input
					className={`${componentClass}__input`}
					type={'radio'}
					readOnly
				/>
				<label className={radioLabelClass}>
					{radioLabel ? radioLabel : __('Please enter radio label in sidebar or this radio will not show on the frontend.', 'eightshift-forms')}
				</label>
			</div>
		</div>
	);
};
