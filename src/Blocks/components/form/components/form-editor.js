import React from 'react';
import { select } from '@wordpress/data';
import classnames from 'classnames';
import { selector, checkAttr, STORE_NAME } from '@eightshift/frontend-libs/scripts';

export const FormEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('form');

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
