import React from 'react';
import classnames from 'classnames';
import { selector, checkAttr } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FieldEditor = (attributes) => {
	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		blockClass,
		additionalClass,
	} = attributes;

	const fieldLabel = checkAttr('fieldLabel', attributes, manifest);
	const fieldContent = checkAttr('fieldContent', attributes, manifest);

	const fieldClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	]);

	return (
		<div
			className={fieldClass}
		>
			{fieldLabel &&
				<label className={`${componentClass}__label`}>
					{fieldLabel}
				</label>
			}
			<div className={`${componentClass}__content`}>
				{fieldContent}
			</div>
		</div>
	);
};
