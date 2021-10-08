import React from 'react';
import classnames from 'classnames';
import { selector, checkAttr, props } from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';
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

	const inputValue = checkAttr('inputValue', attributes, manifest);
	const inputPlaceholder = checkAttr('inputPlaceholder', attributes, manifest);
	let inputType = checkAttr('inputType', attributes, manifest);

	// For some reason React won't allow input type email.
	if (inputType === 'email') {
		inputType = 'text';
	}

	const inputClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	]);

	const input = (
		<input
			className={inputClass}
			value={inputValue}
			placeholder={inputPlaceholder}
			type={inputType}
			readOnly
		/>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: input,
				})}
			/>
		</>
	);
};
