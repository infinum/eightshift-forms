import React from 'react';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { MissingName, preventSaveOnMissingProps } from './../../utils';
import manifest from '../manifest.json';

export const StepEditor = (attributes) => {
	const { blockClientId } = attributes;

	const stepName = checkAttr('stepName', attributes, manifest);
	const stepLabel = checkAttr('stepLabel', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('stepName', attributes, manifest), stepName);

	return (
		<div className='esf:py-5! esf:text-lg! esf:text-secondary-900! esf:flex! esf:flex-col! esf:items-center! esf:gap-2! esf:before:content-[""]! esf:before:bg-secondary-200! esf:before:block! esf:before:w-full! esf:before:h-1! e esf:before:absolute! esf:before:top-1/2! esf:before:left-0!'>
			<div className='esf:font-bold! esf:border! esf:border-secondary-200! esf:p-10! esf:rounded-md! esf:bg-white! esf:z-10! esf:relative!'>
				{stepLabel ? stepLabel : stepName}
			</div>

			<MissingName value={stepName} />
		</div>
	);
};
