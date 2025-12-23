/* global esFormsLocalization */

import React from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { Select, RichLabel, ContainerPanel, InputField, Toggle, Spacer } from '@eightshift/ui-components';
import { icons } from '@eightshift/ui-components/icons';
import {
	FieldOptions,
	FieldOptionsMore,
	FieldOptionsLayout,
	FieldOptionsVisibility,
} from '../../field/components/field-options';
import { isOptionDisabled, NameField } from '../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import manifest from '../manifest.json';

export const PhoneOptions = (attributes) => {
	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const phoneName = checkAttr('phoneName', attributes, manifest);
	const phoneValue = checkAttr('phoneValue', attributes, manifest);
	const phonePlaceholder = checkAttr('phonePlaceholder', attributes, manifest);
	const phoneIsNumber = checkAttr('phoneIsNumber', attributes, manifest); // Used in validation class to validate if the input is a number.
	const phoneIsDisabled = checkAttr('phoneIsDisabled', attributes, manifest);
	const phoneIsReadOnly = checkAttr('phoneIsReadOnly', attributes, manifest);
	const phoneIsRequired = checkAttr('phoneIsRequired', attributes, manifest);
	const phoneTracking = checkAttr('phoneTracking', attributes, manifest);
	const phoneValidationPattern = checkAttr('phoneValidationPattern', attributes, manifest);
	const phoneDisabledOptions = checkAttr('phoneDisabledOptions', attributes, manifest);
	const phoneUseSearch = checkAttr('phoneUseSearch', attributes, manifest);
	const phoneUseLabelAsPlaceholder = checkAttr('phoneUseLabelAsPlaceholder', attributes, manifest);
	const phoneSelectValue = checkAttr('phoneSelectValue', attributes, manifest);
	const phoneValueType = checkAttr('phoneValueType', attributes, manifest);
	const phoneViewType = checkAttr('phoneViewType', attributes, manifest);

	let phoneValidationPatternOptions = [];

	if (typeof esFormsLocalization !== 'undefined') {
		phoneValidationPatternOptions = esFormsLocalization.validationPatternsOptions;
	}

	return (
		<ContainerPanel>
			<Spacer
				border
				icon={icons.options}
				text={__('General', 'eightshift-forms')}
			/>
			<NameField
				value={phoneName}
				attribute={getAttrKey('phoneName', attributes, manifest)}
				disabledOptions={phoneDisabledOptions}
				setAttributes={setAttributes}
				type='phone'
				isChanged={isNameChanged}
				setIsChanged={setIsNameChanged}
			/>

			<FieldOptions
				{...props('field', attributes, {
					fieldDisabledOptions: phoneDisabledOptions,
				})}
			/>

			{!phoneUseLabelAsPlaceholder && (
				<InputField
					help={__('Shown when the field is empty', 'eightshift-forms')}
					value={phonePlaceholder}
					onChange={(value) => setAttributes({ [getAttrKey('phonePlaceholder', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('phonePlaceholder', attributes, manifest), phoneDisabledOptions)}
				/>
			)}
			<Toggle
				icon={icons.fieldPlaceholder}
				label={__('Use label as placeholder', 'eightshift-forms')}
				checked={phoneUseLabelAsPlaceholder}
				onChange={(value) => {
					setAttributes({ [getAttrKey('phonePlaceholder', attributes, manifest)]: undefined });
					setAttributes({ [getAttrKey('phoneUseLabelAsPlaceholder', attributes, manifest)]: value });
				}}
			/>

			<FieldOptionsLayout
				{...props('field', attributes, {
					fieldDisabledOptions: phoneDisabledOptions,
				})}
			/>

			<Spacer
				border
				icon={icons.tools}
				text={__('Advanced', 'eightshift-forms')}
			/>

			<InputField
				label={
					<RichLabel
						icon={icons.fieldValue}
						label={__('Initial value', 'eightshift-forms')}
					/>
				}
				type='number'
				min='1'
				value={phoneValue}
				onChange={(value) => setAttributes({ [getAttrKey('phoneValue', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('phoneValue', attributes, manifest), phoneDisabledOptions)}
				help={__('Initial value of the field in phone number format (e.g. "1234567890").', 'eightshift-forms')}
			/>

			<InputField
				label={
					<RichLabel
						icon={icons.titleGeneric}
						label={__('Dropdown initial value', 'eightshift-forms')}
					/>
				}
				value={phoneSelectValue}
				onChange={(value) => setAttributes({ [getAttrKey('phoneSelectValue', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('phoneSelectValue', attributes, manifest), phoneDisabledOptions)}
				help={__(
					'Initial value of the field in country code format (e.g. "hr"). Only one value is allowed. Phone dropdown can\'t be empty so if no value is provided, the first option will be selected. If geolocation is enabled it will be preselected based on the user\'s location.',
					'eightshift-forms',
				)}
			/>

			<Select
				icon={icons.visible}
				label={__('View type', 'eightshift-forms')}
				help={__('Select the type of view for the phone field.', 'eightshift-forms')}
				options={[
					{
						value: 'number',
						label: __('Number', 'eightshift-forms'),
					},
					{
						value: 'number-country-code',
						label: __('Number with country code', 'eightshift-forms'),
					},
					{
						value: 'number-country-label',
						label: __('Number with country label', 'eightshift-forms'),
					},
				]}
				value={phoneViewType}
				onChange={(value) => setAttributes({ [getAttrKey('phoneViewType', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('phoneViewType', attributes, manifest), phoneDisabledOptions)}
				simpleValue
				noSearch
			/>

			<Select
				icon={icons.migrationAlt}
				value={phoneValueType}
				onChange={(value) => setAttributes({ [getAttrKey('phoneValueType', attributes, manifest)]: value })}
				label={__('Output value type', 'eightshift-forms')}
				help={__('Determine what value to sent on form submission.', 'eightshift-forms')}
				options={[
					{
						value: 'countryNumber',
						label: __('Country number', 'eightshift-forms'),
					},
					{
						value: 'countryNumberWithPlusPrefix',
						label: __('Country number with "+" prefix', 'eightshift-forms'),
					},
				]}
				simpleValue
			/>

			<FieldOptionsVisibility
				{...props('field', attributes, {
					fieldDisabledOptions: phoneDisabledOptions,
				})}
			/>

			<Toggle
				icon={icons.readOnly}
				label={__('Read-only', 'eightshift-forms')}
				checked={phoneIsReadOnly}
				onChange={(value) => setAttributes({ [getAttrKey('phoneIsReadOnly', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('phoneIsReadOnly', attributes, manifest), phoneDisabledOptions)}
			/>

			<Toggle
				icon={icons.cursorDisabled}
				label={__('Disabled', 'eightshift-forms')}
				checked={phoneIsDisabled}
				onChange={(value) => setAttributes({ [getAttrKey('phoneIsDisabled', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('phoneIsDisabled', attributes, manifest), phoneDisabledOptions)}
			/>

			<Toggle
				icon={icons.order}
				label={__('Number', 'eightshift-forms')}
				checked={phoneIsNumber}
				onChange={(value) => setAttributes({ [getAttrKey('phoneIsNumber', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('phoneIsNumber', attributes, manifest), phoneDisabledOptions)}
			/>

			<Toggle
				icon={icons.search}
				label={__('Search', 'eightshift-forms')}
				checked={phoneUseSearch}
				onChange={(value) => setAttributes({ [getAttrKey('phoneUseSearch', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('phoneUseSearch', attributes, manifest), phoneDisabledOptions)}
			/>

			<Spacer
				border
				icon={icons.checks}
				text={__('Validation', 'eightshift-forms')}
			/>
			<Toggle
				icon={icons.required}
				label={__('Required', 'eightshift-forms')}
				checked={phoneIsRequired}
				onChange={(value) => setAttributes({ [getAttrKey('phoneIsRequired', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('phoneIsRequired', attributes, manifest), phoneDisabledOptions)}
			/>

			<Select
				icon={icons.regex}
				label={__('Match pattern', 'eightshift-forms')}
				options={phoneValidationPatternOptions}
				value={phoneValidationPattern}
				onChange={(value) => setAttributes({ [getAttrKey('phoneValidationPattern', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('phoneValidationPattern', attributes, manifest), phoneDisabledOptions)}
				placeholder='–'
				clearable
			/>

			<Spacer
				border
				icon={icons.alignHorizontalVertical}
				text={__('Tracking', 'eightshift-forms')}
			/>

			<InputField
				label={
					<RichLabel
						icon={icons.googleTagManager}
						label={__('GTM tracking code', 'eightshift-forms')}
					/>
				}
				value={phoneTracking}
				onChange={(value) => setAttributes({ [getAttrKey('phoneTracking', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('phoneTracking', attributes, manifest), phoneDisabledOptions)}
			/>

			<FieldOptionsMore
				{...props('field', attributes, {
					fieldDisabledOptions: phoneDisabledOptions,
				})}
			/>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: phoneName,
					conditionalTagsIsHidden: checkAttr('phoneFieldHidden', attributes, manifest),
				})}
			/>
		</ContainerPanel>
	);
};
