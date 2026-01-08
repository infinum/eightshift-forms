/* global esFormsLocalization */

import React from 'react';
import { checkAttr } from '@eightshift/frontend-libs-tailwind/scripts';
import globalSettings from '../../../manifest.json';
import manifest from '../manifest.json';

export const additionalBlocksNoIntegration = [
	...esFormsLocalization.additionalBlocks,
	...globalSettings.allowedBlocksList.fieldsNoIntegration,
];

export const additionalBlocksIntegration = [
	...esFormsLocalization.additionalBlocks,
	...globalSettings.allowedBlocksList.fieldsIntegration,
];

export const FormEditor = (attributes) => {
	const formContent = checkAttr('formContent', attributes, manifest);

	return <div className='esf-form'>{formContent}</div>;
};
