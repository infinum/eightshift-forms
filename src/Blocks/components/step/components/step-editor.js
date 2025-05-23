import React from 'react';
import { select } from '@wordpress/data';
import { checkAttr, STORE_NAME, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { MissingName, preventSaveOnMissingProps } from './../../utils';

export const StepEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('step');

	const { blockClientId } = attributes;

	const stepName = checkAttr('stepName', attributes, manifest);
	const stepLabel = checkAttr('stepLabel', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('stepName', attributes, manifest), stepName);

	return (
		<div>
			<div>{stepLabel ? stepLabel : stepName}</div>

			<MissingName
				value={stepName}
			/>
		</div>
	);
};
