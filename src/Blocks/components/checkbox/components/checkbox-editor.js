import React from 'react';
import classnames from 'classnames';
import { selector, checkAttr } from '@eightshift/frontend-libs/scripts';
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
	

	return (
		<div className={checkboxClass}>
			<div className={`${componentClass}__content`}>
				<label className={`${componentClass}__label`}>
					{checkboxLabel}
				</label>
				<input
					className={`${componentClass}__input`}
					type={'checkbox'}
					readOnly
				/>
			</div>
		</div>
	);
};
