import React from 'react';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import {
	StatusFieldOutput,
	StatusIconHidden,
	StatusIconConditionals,
	StatusIconMissingName,
	preventSaveOnMissingProps,
} from './../../utils';
import manifest from '../manifest.json';
import { clsx } from '@eightshift/ui-components/utilities';

export const SelectOptionEditor = (attributes) => {
	const { blockClientId, prefix } = attributes;

	const selectOptionLabel = checkAttr('selectOptionLabel', attributes, manifest);
	const selectOptionValue = checkAttr('selectOptionValue', attributes, manifest);
	const selectOptionIsHidden = checkAttr('selectOptionIsHidden', attributes, manifest);
	const selectOptionIsSelected = checkAttr('selectOptionIsSelected', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('selectOptionValue', attributes, manifest), selectOptionValue);

	return (
		<div
			className={clsx(
				'esf-fieldset-item',
				selectOptionIsHidden && 'esf-field-hidden',
				selectOptionIsSelected && 'esf:text-accent-600!',
			)}
		>
			{selectOptionLabel ? selectOptionLabel : __('Enter option label in sidebar.', 'eightshift-forms')}

			<StatusFieldOutput
				components={[
					selectOptionIsHidden && <StatusIconHidden />,
					!selectOptionValue && <StatusIconMissingName />,
					attributes?.[`${prefix}ConditionalTagsUse`] && <StatusIconConditionals />,
				]}
			/>
		</div>
	);
};
