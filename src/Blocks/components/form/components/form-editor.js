/* global esFormsLocalization */

import React from 'react';
import { select } from '@wordpress/data';
import { checkAttr, STORE_NAME } from '@eightshift/frontend-libs-tailwind/scripts';
import globalSettings from '../../../manifest.json';

export const additionalBlocksNoIntegration = [...esFormsLocalization.additionalBlocks, ...globalSettings.allowedBlocksList.fieldsNoIntegration];

export const additionalBlocksIntegration = [...esFormsLocalization.additionalBlocks, ...globalSettings.allowedBlocksList.fieldsIntegration];

export const FormEditor = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('form');

	const formContent = checkAttr('formContent', attributes, manifest);

	return (
		<div>
			<div>{formContent}</div>
		</div>
	);
};
