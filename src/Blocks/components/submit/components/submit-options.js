import React from 'react';
import { __ } from '@wordpress/i18n';
import { icons } from '@eightshift/ui-components/icons';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldOptionsMore, FieldOptionsLayout, FieldOptionsVisibility } from '../../field/components/field-options';
import { isOptionDisabled } from './../../utils';
import { ContainerPanel, InputField, Toggle, Spacer } from '@eightshift/ui-components';
import manifest from '../manifest.json';

export const SubmitOptions = (attributes) => {
	const { setAttributes } = attributes;

	const submitValue = checkAttr('submitValue', attributes, manifest);
	const submitIsDisabled = checkAttr('submitIsDisabled', attributes, manifest);
	const submitTracking = checkAttr('submitTracking', attributes, manifest);
	const submitDisabledOptions = checkAttr('submitDisabledOptions', attributes, manifest);

	return (
		<ContainerPanel>
			<Spacer
				border
				icon={icons.options}
				text={__('General', 'eightshift-forms')}
			/>
			<InputField
				icon={icons.titleGeneric}
				label={__('Button label', 'eightshift-forms')}
				value={submitValue}
				onChange={(value) => setAttributes({ [getAttrKey('submitValue', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('submitValue', attributes, manifest), submitDisabledOptions)}
			/>

			<FieldOptionsLayout
				{...props('field', attributes, {
					fieldDisabledOptions: submitDisabledOptions,
				})}
			/>

			<Spacer
				border
				icon={icons.tools}
				text={__('Advanced', 'eightshift-forms')}
			/>

			<FieldOptionsVisibility
				{...props('field', attributes, {
					fieldDisabledOptions: submitDisabledOptions,
				})}
			/>

			<Toggle
				icon={icons.cursorDisabled}
				label={__('Disabled', 'eightshift-forms')}
				checked={submitIsDisabled}
				onChange={(value) => setAttributes({ [getAttrKey('submitIsDisabled', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('submitIsDisabled', attributes, manifest), submitDisabledOptions)}
			/>

			<Spacer
				border
				icon={icons.alignHorizontalVertical}
				text={__('Tracking', 'eightshift-forms')}
			/>
			<InputField
				icon={icons.googleTagManager}
				label={__('GTM tracking code', 'eightshift-forms')}
				placeholder={__('Enter GTM tracking code', 'eightshift-forms')}
				value={submitTracking}
				onChange={(value) => setAttributes({ [getAttrKey('submitTracking', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('submitTracking', attributes, manifest), submitDisabledOptions)}
			/>

			<FieldOptionsMore
				{...props('field', attributes, {
					fieldDisabledOptions: submitDisabledOptions,
				})}
			/>
		</ContainerPanel>
	);
};
