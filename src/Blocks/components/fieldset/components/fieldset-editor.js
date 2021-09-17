import React from 'react';
import classnames from 'classnames';
import { selector, checkAttr } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FieldsetEditor = (attributes) => {
	const {
		componentClass,
	} = manifest;

	const {
		selectorClass = componentClass,
		blockClass,
		additionalClass,
	} = attributes;

	const fieldsetLegend = checkAttr('fieldsetLegend', attributes, manifest);
	const fieldsetId = checkAttr('fieldsetId', attributes, manifest);
	const fieldsetContent = checkAttr('fieldsetContent', attributes, manifest);

	const fieldsetClass = classnames([
		selector(componentClass, componentClass),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	]);

	return (
		<fieldset
			className={fieldsetClass}
			id={fieldsetId}
		>
			{fieldsetLegend &&
				<legend className={`${componentClass}__legend`}>
					{fieldsetLegend}
				</legend>
			}
			<div className={`${componentClass}__content`}>
				{fieldsetContent}
			</div>
		</fieldset>
	);
};
