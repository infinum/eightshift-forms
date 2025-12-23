import React from 'react';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import {
	StatusFieldOutput,
	StatusIconConditionals,
	StatusIconHidden,
	StatusIconMissingName,
	preventSaveOnMissingProps,
} from './../../utils';
import manifest from '../manifest.json';
import { clsx } from '@eightshift/ui-components/utilities';

export const RadioEditor = (attributes) => {
	const { blockClientId, prefix } = attributes;

	const radioLabel = checkAttr('radioLabel', attributes, manifest);
	const radioValue = checkAttr('radioValue', attributes, manifest);
	const radioIsHidden = checkAttr('radioIsHidden', attributes, manifest);
	const radioIsChecked = checkAttr('radioIsChecked', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('radioValue', attributes, manifest), radioValue);

	return (
		<div
			className={clsx(
				'esf-fieldset-radio',
				'esf-fieldset-checkbox',
				'esf-fieldset-item',
				'esf:relative!',
				radioIsHidden && 'esf-field-hidden',
				radioIsChecked && 'esf-fieldset-checked',
			)}
		>
			<span
				dangerouslySetInnerHTML={{
					__html: radioLabel
						? radioLabel
						: __(
								'Please enter radio label in sidebar or this radio will not show on the frontend.',
								'eightshift-forms',
							),
				}}
			/>
			<StatusFieldOutput
				components={[
					radioIsHidden && <StatusIconHidden />,
					!radioValue && <StatusIconMissingName />,
					attributes?.[`${prefix}ConditionalTagsUse`] && <StatusIconConditionals />,
				]}
			/>
		</div>
	);
};
