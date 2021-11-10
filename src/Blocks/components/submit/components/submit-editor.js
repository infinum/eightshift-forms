import React from 'react';
import classnames from 'classnames';
import { selector, checkAttr, props } from '@eightshift/frontend-libs/scripts';
import { FieldEditor } from '../../../components/field/components/field-editor';
import manifest from '../manifest.json';

export const SubmitEditor = (attributes) => {
	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		blockClass,
		additionalClass,
	} = attributes;

	const submitValue = checkAttr('submitValue', attributes, manifest);

	const submitClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	]);

	const button = (
		<button className={submitClass}>
			{submitValue}
		</button>
	);

	return (
		<>
			<FieldEditor
				{...props('field', attributes, {
					fieldContent: button
				})}
			/>
		</>
	);
};
