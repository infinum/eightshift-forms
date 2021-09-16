import React from 'react';
import classnames from 'classnames';
import { selector, checkAttr } from '@eightshift/frontend-libs/scripts';
import manifest from './../manifest.json';

export const InputEditor = (attributes) => {
	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		blockClass,
		additionalClass,
	} = attributes;

	const inputName = checkAttr('inputName', attributes, manifest);
	const inputValue = checkAttr('inputValue', attributes, manifest);
	const inputId = checkAttr('inputId', attributes, manifest);
	const inputPlaceholder = checkAttr('inputPlaceholder', attributes, manifest);
	const inputType = checkAttr('inputType', attributes, manifest);

	const inputClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	]);

	return (
		<>
			<input
				className={inputClass}
				name={inputName}
				value={inputValue}
				id={inputId}
				placeholder={inputPlaceholder}
				type={inputType}
				disabled
			/>
		</>
	);
};
