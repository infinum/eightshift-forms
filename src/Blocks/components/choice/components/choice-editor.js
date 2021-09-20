import React from 'react';
import classnames from 'classnames';
import { selector, checkAttr } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const ChoiceEditor = (attributes) => {
	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		blockClass,
		additionalClass,
	} = attributes;

	const choiceLabel = checkAttr('choiceLabel', attributes, manifest);
	const choiceName = checkAttr('choiceName', attributes, manifest);
	const choiceType = checkAttr('choiceType', attributes, manifest);
	const choiceId = checkAttr('choiceId', attributes, manifest);

	const choiceClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	]);
	

	return (
		<div className={choiceClass}>
			<label
				htmlFor={choiceName}
				className={`${componentClass}__label`}
			>
				{choiceLabel}
			</label>
			<input
				className={`${componentClass}__input`}
				type={choiceType}
				id={choiceId}
				readOnly
			/>
		</div>
	);
};
