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
		<div className={'es:flex es:items-center es:justify-between es:mb-5'}>
			<div className={'es:p-5 es:text-lg es:text-center es:font-medium es:w-full es:bg-accent-600 es:text-white'}>{stepLabel ? stepLabel : stepName}</div>

			<MissingName value={stepName} />
		</div>
	);
};
