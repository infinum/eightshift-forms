import React from 'react';
import classnames from 'classnames';
import { selector, checkAttr } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FormEditor = (attributes) => {
	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		blockClass,
		additionalClass,
	} = attributes;

	const formContent = checkAttr('formContent', attributes, manifest);

	const formClass = classnames([
		selector(componentClass, componentClass),
		selector(componentClass, componentClass, 'editor'),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	]);

	return (
		<div className={formClass}>
			<div className={`${componentClass}__fields`}>
				{formContent}
			</div>
		</div>
	);
};
