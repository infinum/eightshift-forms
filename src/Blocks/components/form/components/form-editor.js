/* global esFormsLocalization */

import { checkAttr } from '@eightshift/frontend-libs-tailwind/scripts';
import { useBlockProps } from '@wordpress/block-editor';
import globalSettings from '../../../manifest.json';
import manifest from '../manifest.json';

export const additionalBlocksNoIntegration = [...esFormsLocalization.additionalBlocks, ...globalSettings.allowedBlocksList.fieldsNoIntegration];
export const additionalBlocksIntegration = [...esFormsLocalization.additionalBlocks, ...globalSettings.allowedBlocksList.fieldsIntegration];

export const FormEditor = (attributes) => {
	const formContent = checkAttr('formContent', attributes, manifest);

	const blockProps = useBlockProps({
		className: 'esf-form',
	});

	return <div {...blockProps}>{formContent}</div>;
};
