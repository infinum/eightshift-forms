import React from 'react';
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
	const radioName = checkAttr('radioName', attributes, manifest);
	const radioId = checkAttr('radioId', attributes, manifest);

	const radioClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	]);
	

	return (
		<div className={radioClass}>
			<label
				htmlFor={radioName}
				className={`${componentClass}__label`}
			>
				{radioLabel}
			</label>
			<input
				className={`${componentClass}__input`}
				type={'radio'}
				id={radioId}
				readOnly
			/>
		</div>
	);
};
