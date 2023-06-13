import React from 'react';
import classnames from 'classnames';
import {
	selector,
	checkAttr,
} from '@eightshift/frontend-libs/scripts';
import { MissingName } from './../../utils';
import manifest from '../manifest.json';

export const StepEditor = (attributes) => {
	const {
		componentClass,
	} = manifest;

	const {
		additionalClass,
	} = attributes;

	const stepName = checkAttr('stepName', attributes, manifest);
	const stepLabel = checkAttr('stepLabel', attributes, manifest);

	const stepClass = classnames([
		selector(componentClass, componentClass),
		selector(additionalClass, additionalClass),
	]);

	return (
		<div className={stepClass}>
			<div className={`${componentClass}__inner`}>
				{stepLabel ? stepLabel : stepName}
			</div>

			<MissingName value={stepName} className={`${componentClass}__missing`} />
		</div>
	);
};
