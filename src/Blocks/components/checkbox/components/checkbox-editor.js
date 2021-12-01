import React  from 'react';
import { __ } from '@wordpress/i18n';
import classnames from 'classnames';
import {
	selector,
	checkAttr
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const CheckboxEditor = (attributes) => {
	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		blockClass,
		additionalClass,
	} = attributes;

	const checkboxLabel = checkAttr('checkboxLabel', attributes, manifest);

	const checkboxClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	]);

	const checkboxLabelClass = classnames([
		selector(componentClass, componentClass, 'label'),
		selector(checkboxLabel === '', componentClass, 'label', 'placeholder'),
	]);

	const label = <label className={checkboxLabelClass} dangerouslySetInnerHTML={{__html: checkboxLabel ? checkboxLabel : __('Please enter checkbox label in sidebar or this checkbox will not show on the frontend.', 'eightshift-forms')}} />; // eslint-disable-line jsx-a11y/label-has-associated-control

	return (
		<div className={checkboxClass}>
			<div className={`${componentClass}__content`}>
				<input
					className={`${componentClass}__input`}
					type={'checkbox'}
					readOnly
				/>
				{label}
			</div>
		</div>
	);
};
