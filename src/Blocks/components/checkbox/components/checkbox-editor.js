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

export const CheckboxEditor = (attributes) => {
	const { blockClientId, prefix } = attributes;

	const checkboxLabel = checkAttr('checkboxLabel', attributes, manifest);
	const checkboxValue = checkAttr('checkboxValue', attributes, manifest);
	const checkboxIsHidden = checkAttr('checkboxIsHidden', attributes, manifest);
	const checkboxIsChecked = checkAttr('checkboxIsChecked', attributes, manifest);

	preventSaveOnMissingProps(blockClientId, getAttrKey('checkboxValue', attributes, manifest), checkboxValue);

	return (
		<div
			className={clsx(
				'esf-fieldset-checkbox',
				'esf-fieldset-item',
				'esf:relative!',
				checkboxIsHidden && 'esf-field-hidden',
				checkboxIsChecked && 'esf-fieldset-checked',
			)}
		>
			<span
				dangerouslySetInnerHTML={{
					__html: checkboxLabel
						? checkboxLabel
						: __(
								'Please enter checkbox label in sidebar or this checkbox will not show on the frontend.',
								'eightshift-forms',
							),
				}}
			/>
			<StatusFieldOutput
				components={[
					checkboxIsHidden && <StatusIconHidden />,
					!checkboxValue && <StatusIconMissingName />,
					attributes?.[`${prefix}ConditionalTagsUse`] && <StatusIconConditionals />,
				]}
			/>
		</div>
	);
};
