import React from 'react';
import { select } from '@wordpress/data';
import { selector, checkAttr, STORE_NAME, getAttrKey } from '@eightshift/frontend-libs/scripts';
import { clsx } from '@eightshift/ui-components/utilities';
import { MissingName, preventSaveOnMissingProps } from './../../utils';

export const StepEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('step');

	const { componentClass } = manifest;

	const { additionalClass, blockClientId } = attributes;

	const stepName = checkAttr('stepName', attributes, manifest);
	const stepLabel = checkAttr('stepLabel', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('stepName', attributes, manifest), stepName);

	const stepClass = clsx(selector(componentClass, componentClass), selector(additionalClass, additionalClass));

	return (
		<div className={stepClass}>
			<div className={`${componentClass}__inner`}>{stepLabel ? stepLabel : stepName}</div>

			<MissingName
				value={stepName}
				className={`${componentClass}__missing`}
			/>
		</div>
	);
};
