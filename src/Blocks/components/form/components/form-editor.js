/* global esFormsLocalization */

import React from 'react';
import { select } from '@wordpress/data';
import { selector, checkAttr, STORE_NAME } from '@eightshift/frontend-libs-tailwind/scripts';
import { clsx } from '@eightshift/ui-components/utilities';
import globalSettings from '../../../manifest.json';

export const additionalBlocksNoIntegration = [...esFormsLocalization.additionalBlocks, ...globalSettings.allowedBlocksList.fieldsNoIntegration];

export const additionalBlocksIntegration = [...esFormsLocalization.additionalBlocks, ...globalSettings.allowedBlocksList.fieldsIntegration];

export const FormEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('form');

	const { componentClass } = manifest;

	const { selectorClass = componentClass, blockClass, additionalClass } = attributes;

	const formContent = checkAttr('formContent', attributes, manifest);

	const formClass = clsx(
		selector(componentClass, componentClass),
		selector(componentClass, componentClass, 'editor'),
		selector(blockClass, blockClass, selectorClass),
		selector(additionalClass, additionalClass),
	);

	return (
		<div className={formClass}>
			<div className={`${componentClass}__fields`}>{formContent}</div>
		</div>
	);
};
