import React from 'react';
import classnames from 'classnames';
import { selector, checkAttr } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const ErrorEditor = (attributes) => {
	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		blockClass,
		additionalClass,
	} = attributes;

	const errorValue = checkAttr('errorValue', attributes, manifest);
	const errorId = checkAttr('errorId', attributes, manifest);

	const errorClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	]);

	return (
		<div
			className={errorClass}
			data-id={errorId}
		>
			{errorValue}
		</div>
	);
};
