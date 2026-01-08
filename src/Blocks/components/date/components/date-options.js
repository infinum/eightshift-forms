/* global esFormsLocalization */

import React from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey, props, getOption } from '@eightshift/frontend-libs-tailwind/scripts';
import {
	FieldOptions,
	FieldOptionsMore,
	FieldOptionsLayout,
	FieldOptionsVisibility,
} from '../../field/components/field-options';
import { Select, Button, ContainerPanel, InputField, Toggle, ContainerGroup, Spacer } from '@eightshift/ui-components';
import { icons } from '@eightshift/ui-components/icons';
import { isOptionDisabled, NameField } from '../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import manifest from '../manifest.json';

export const DateOptions = (attributes) => {
	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const dateName = checkAttr('dateName', attributes, manifest);
	const dateValue = checkAttr('dateValue', attributes, manifest);
	const datePlaceholder = checkAttr('datePlaceholder', attributes, manifest);
	const dateIsDisabled = checkAttr('dateIsDisabled', attributes, manifest);
	const dateIsReadOnly = checkAttr('dateIsReadOnly', attributes, manifest);
	const dateIsRequired = checkAttr('dateIsRequired', attributes, manifest);
	const dateTracking = checkAttr('dateTracking', attributes, manifest);
	const dateValidationPattern = checkAttr('dateValidationPattern', attributes, manifest);
	const dateDisabledOptions = checkAttr('dateDisabledOptions', attributes, manifest);
	const dateType = checkAttr('dateType', attributes, manifest);
	const dateUseLabelAsPlaceholder = checkAttr('dateUseLabelAsPlaceholder', attributes, manifest);
	const datePreviewFormat = checkAttr('datePreviewFormat', attributes, manifest);
	const dateOutputFormat = checkAttr('dateOutputFormat', attributes, manifest);
	const dateMode = checkAttr('dateMode', attributes, manifest);

	let dateValidationPatternOptions = [];

	if (typeof esFormsLocalization !== 'undefined') {
		dateValidationPatternOptions = esFormsLocalization.validationPatternsOptions;
	}

	return (
		<ContainerPanel>
			<Spacer
				border
				icon={icons.options}
				text={__('General', 'eightshift-forms')}
			/>

			<NameField
				value={dateName}
				attribute={getAttrKey('dateName', attributes, manifest)}
				disabledOptions={dateDisabledOptions}
				setAttributes={setAttributes}
				type='date'
				isChanged={isNameChanged}
				setIsChanged={setIsNameChanged}
			/>

			<Select
				icon={icons.optionListAlt}
				label={__('Type', 'eightshift-forms')}
				value={dateType}
				options={getOption('dateType', attributes, manifest)}
				disabled={isOptionDisabled(getAttrKey('dateType', attributes, manifest), dateDisabledOptions)}
				onChange={(value) => setAttributes({ [getAttrKey('dateType', attributes, manifest)]: value })}
				simpleValue
				noSearch
			/>

			<FieldOptions
				{...props('field', attributes, {
					fieldDisabledOptions: dateDisabledOptions,
				})}
			/>

			<ContainerGroup>
				<Toggle
					icon={icons.fieldPlaceholder}
					label={__('Use label as placeholder', 'eightshift-forms')}
					checked={dateUseLabelAsPlaceholder}
					onChange={(value) => {
						setAttributes({ [getAttrKey('datePlaceholder', attributes, manifest)]: undefined });
						setAttributes({ [getAttrKey('dateUseLabelAsPlaceholder', attributes, manifest)]: value });
					}}
				/>
				{!dateUseLabelAsPlaceholder && (
					<InputField
						placeholder={__('Enter placeholder', 'eightshift-forms')}
						help={__('Shown when the field is empty', 'eightshift-forms')}
						value={datePlaceholder}
						onChange={(value) => setAttributes({ [getAttrKey('datePlaceholder', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('datePlaceholder', attributes, manifest), dateDisabledOptions)}
					/>
				)}
			</ContainerGroup>

			<FieldOptionsLayout
				{...props('field', attributes, {
					fieldDisabledOptions: dateDisabledOptions,
				})}
			/>

			<Spacer
				border
				icon={icons.tools}
				text={__('Advanced', 'eightshift-forms')}
			/>

			<InputField
				icon={icons.fieldValue}
				label={__('Initial value', 'eightshift-forms')}
				placeholder={__('Enter initial value', 'eightshift-forms')}
				value={dateValue}
				onChange={(value) => setAttributes({ [getAttrKey('dateValue', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('dateValue', attributes, manifest), dateDisabledOptions)}
			/>

			<Select
				label={__('Mode', 'eightshift-forms')}
				value={dateMode}
				options={getOption('dateMode', attributes, manifest)}
				disabled={isOptionDisabled(getAttrKey('dateMode', attributes, manifest), dateDisabledOptions)}
				onChange={(value) => setAttributes({ [getAttrKey('dateMode', attributes, manifest)]: value })}
				simpleValue
				noSearch
			/>

			<FieldOptionsVisibility
				{...props('field', attributes, {
					fieldDisabledOptions: dateDisabledOptions,
				})}
			/>

			<Toggle
				icon={icons.readOnly}
				label={__('Read-only', 'eightshift-forms')}
				checked={dateIsReadOnly}
				onChange={(value) => setAttributes({ [getAttrKey('dateIsReadOnly', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('dateIsReadOnly', attributes, manifest), dateDisabledOptions)}
			/>

			<Toggle
				icon={icons.cursorDisabled}
				label={__('Disabled', 'eightshift-forms')}
				checked={dateIsDisabled}
				onChange={(value) => setAttributes({ [getAttrKey('dateIsDisabled', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('dateIsDisabled', attributes, manifest), dateDisabledOptions)}
			/>

			<Button
				href={`https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date#date_time_string_format`}
				target='_blank'
			>
				{__('View valid formats', 'eightshift-forms')}
			</Button>

			<InputField
				label={__('Preview format', 'eightshift-forms')}
				icon={icons.dateTime}
				value={datePreviewFormat}
				placeholder={manifest.formats[dateType].preview}
				help={__('Define format of date/time the user will see', 'eightshift-forms')}
				onChange={(value) => setAttributes({ [getAttrKey('datePreviewFormat', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('datePreviewFormat', attributes, manifest), dateDisabledOptions)}
			/>

			<InputField
				icon={icons.dateTime}
				label={__('Output format', 'eightshift-forms')}
				value={dateOutputFormat}
				placeholder={manifest.formats[dateType].output}
				help={__('Define format of date/time that will be sent when form is processed', 'eightshift-forms')}
				onChange={(value) => setAttributes({ [getAttrKey('dateOutputFormat', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('dateOutputFormat', attributes, manifest), dateDisabledOptions)}
			/>

			<Spacer
				border
				icon={icons.checks}
				text={__('Validation', 'eightshift-forms')}
			/>

			<Toggle
				icon={icons.fieldRequired}
				label={__('Required', 'eightshift-forms')}
				checked={dateIsRequired}
				onChange={(value) => setAttributes({ [getAttrKey('dateIsRequired', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('dateIsRequired', attributes, manifest), dateDisabledOptions)}
			/>

			<Select
				icon={icons.regex}
				label={__('Match pattern', 'eightshift-forms')}
				options={dateValidationPatternOptions}
				value={dateValidationPattern}
				onChange={(value) => setAttributes({ [getAttrKey('dateValidationPattern', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('dateValidationPattern', attributes, manifest), dateDisabledOptions)}
				placeholder='–'
				clearable
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
				value={dateTracking}
				onChange={(value) => setAttributes({ [getAttrKey('dateTracking', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('dateTracking', attributes, manifest), dateDisabledOptions)}
			/>

			<FieldOptionsMore
				{...props('field', attributes, {
					fieldDisabledOptions: dateDisabledOptions,
				})}
			/>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: dateName,
					conditionalTagsIsHidden: checkAttr('dateFieldHidden', attributes, manifest),
				})}
			/>
		</ContainerPanel>
	);
};
