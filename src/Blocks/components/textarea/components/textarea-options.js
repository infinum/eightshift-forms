/* global esFormsLocalization */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { icons } from '@eightshift/ui-components/icons';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import {
	Select,
	NumberPicker,
	HStack,
	InputField,
	Toggle,
	ContainerGroup,
	ContainerPanel,
	Spacer,
} from '@eightshift/ui-components';
import {
	FieldOptions,
	FieldOptionsMore,
	FieldOptionsLayout,
	FieldOptionsVisibility,
} from '../../field/components/field-options';
import { isOptionDisabled, NameField } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import manifest from '../manifest.json';

export const TextareaOptions = (attributes) => {
	const { options } = manifest;

	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const textareaName = checkAttr('textareaName', attributes, manifest);
	const textareaValue = checkAttr('textareaValue', attributes, manifest);
	const textareaPlaceholder = checkAttr('textareaPlaceholder', attributes, manifest);
	const textareaIsDisabled = checkAttr('textareaIsDisabled', attributes, manifest);
	const textareaIsReadOnly = checkAttr('textareaIsReadOnly', attributes, manifest);
	const textareaIsRequired = checkAttr('textareaIsRequired', attributes, manifest);
	const textareaTracking = checkAttr('textareaTracking', attributes, manifest);
	const textareaValidationPattern = checkAttr('textareaValidationPattern', attributes, manifest);
	const textareaDisabledOptions = checkAttr('textareaDisabledOptions', attributes, manifest);
	const textareaMinLength = checkAttr('textareaMinLength', attributes, manifest);
	const textareaMaxLength = checkAttr('textareaMaxLength', attributes, manifest);
	const textareaUseLabelAsPlaceholder = checkAttr('textareaUseLabelAsPlaceholder', attributes, manifest);

	let textareaValidationPatternOptions = [];

	if (typeof esFormsLocalization !== 'undefined') {
		textareaValidationPatternOptions = esFormsLocalization.validationPatternsOptions;
	}

	return (
		<ContainerPanel>
			<Spacer
				border
				icon={icons.options}
				text={__('General', 'eightshift-forms')}
			/>

			<NameField
				value={textareaName}
				attribute={getAttrKey('textareaName', attributes, manifest)}
				disabledOptions={textareaDisabledOptions}
				setAttributes={setAttributes}
				type='textarea'
				isChanged={isNameChanged}
				setIsChanged={setIsNameChanged}
			/>

			<FieldOptions
				{...props('field', attributes, {
					fieldDisabledOptions: textareaDisabledOptions,
				})}
			/>

			<Toggle
				icon={icons.fieldPlaceholder}
				label={__('Use label as placeholder', 'eightshift-forms')}
				checked={textareaUseLabelAsPlaceholder}
				onChange={(value) => {
					setAttributes({ [getAttrKey('textareaPlaceholder', attributes, manifest)]: undefined });
					setAttributes({ [getAttrKey('textareaUseLabelAsPlaceholder', attributes, manifest)]: value });
				}}
			/>

			{!textareaUseLabelAsPlaceholder && (
				<InputField
					placeholder={__('Enter placeholder', 'eightshift-forms')}
					help={__('Shown when the field is empty', 'eightshift-forms')}
					value={textareaPlaceholder}
					onChange={(value) => setAttributes({ [getAttrKey('textareaPlaceholder', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('textareaPlaceholder', attributes, manifest), textareaDisabledOptions)}
				/>
			)}

			<FieldOptionsLayout
				{...props('field', attributes, {
					fieldDisabledOptions: textareaDisabledOptions,
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
				value={textareaValue}
				onChange={(value) => setAttributes({ [getAttrKey('textareaValue', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('textareaValue', attributes, manifest), textareaDisabledOptions)}
			/>

			<FieldOptionsVisibility
				{...props('field', attributes, {
					fieldDisabledOptions: textareaDisabledOptions,
				})}
			/>

			<Toggle
				icon={icons.readOnly}
				label={__('Read-only', 'eightshift-forms')}
				checked={textareaIsReadOnly}
				onChange={(value) => setAttributes({ [getAttrKey('textareaIsReadOnly', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('textareaIsReadOnly', attributes, manifest), textareaDisabledOptions)}
			/>

			<Toggle
				icon={icons.cursorDisabled}
				label={__('Disabled', 'eightshift-forms')}
				checked={textareaIsDisabled}
				onChange={(value) => setAttributes({ [getAttrKey('textareaIsDisabled', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('textareaIsDisabled', attributes, manifest), textareaDisabledOptions)}
			/>

			<Spacer
				border
				icon={icons.checks}
				text={__('Validation', 'eightshift-forms')}
			/>

			<Toggle
				icon={icons.fieldRequired}
				label={__('Required', 'eightshift-forms')}
				checked={textareaIsRequired}
				onChange={(value) => setAttributes({ [getAttrKey('textareaIsRequired', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('textareaIsRequired', attributes, manifest), textareaDisabledOptions)}
			/>

			<Select
				icon={icons.regex}
				label={__('Match pattern', 'eightshift-forms')}
				options={textareaValidationPatternOptions}
				value={textareaValidationPattern}
				onChange={(value) => setAttributes({ [getAttrKey('textareaValidationPattern', attributes, manifest)]: value })}
				disabled={isOptionDisabled(
					getAttrKey('textareaValidationPattern', attributes, manifest),
					textareaDisabledOptions,
				)}
				placeholder='–'
				clearable
			/>

			<HStack>
				<NumberPicker
					label={__('Min length', 'eightshift-forms')}
					value={textareaMinLength}
					onChange={(value) => setAttributes({ [getAttrKey('textareaMinLength', attributes, manifest)]: value })}
					min={options.textareaMinLength.min}
					step={options.textareaMinLength.step}
					disabled={isOptionDisabled(getAttrKey('textareaMinLength', attributes, manifest), textareaDisabledOptions)}
					placeholder='–'
					fixedWidth={4}
					prefix={__('Min length', 'eightshift-forms')}
				>
					<button
						icon={icons.resetToZero}
						tooltip={__('Reset', 'eightshift-forms')}
						onClick={() => setAttributes({ [getAttrKey('textareaMinLength', attributes, manifest)]: undefined })}
						disabled={textareaMinLength === 0}
						type='ghost'
					>
						{__('x', 'eightshift-forms')}
					</button>
				</NumberPicker>

				<NumberPicker
					label={__('Max length', 'eightshift-forms')}
					value={textareaMaxLength}
					onChange={(value) => setAttributes({ [getAttrKey('textareaMaxLength', attributes, manifest)]: value })}
					min={options.textareaMaxLength.min}
					step={options.textareaMaxLength.step}
					disabled={isOptionDisabled(getAttrKey('textareaMaxLength', attributes, manifest), textareaDisabledOptions)}
					placeholder='–'
					fixedWidth={4}
					prefix={__('Max length', 'eightshift-forms')}
				>
					<button
						icon={icons.resetToZero}
						tooltip={__('Reset', 'eightshift-forms')}
						onClick={() => setAttributes({ [getAttrKey('textareaMaxLength', attributes, manifest)]: undefined })}
						disabled={textareaMaxLength === 0}
						type='ghost'
					>
						{__('x', 'eightshift-forms')}
					</button>
				</NumberPicker>
			</HStack>

			<Spacer
				border
				icon={icons.alignHorizontalVertical}
				text={__('Tracking', 'eightshift-forms')}
			/>

			<InputField
				icon={icons.googleTagManager}
				label={__('GTM tracking code', 'eightshift-forms')}
				placeholder={__('Enter GTM tracking code', 'eightshift-forms')}
				value={textareaTracking}
				onChange={(value) => setAttributes({ [getAttrKey('textareaTracking', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('textareaTracking', attributes, manifest), textareaDisabledOptions)}
			/>

			<FieldOptionsMore
				{...props('field', attributes, {
					fieldDisabledOptions: textareaDisabledOptions,
				})}
			/>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: textareaName,
					conditionalTagsIsHidden: checkAttr('textareaFieldHidden', attributes, manifest),
				})}
			/>
		</ContainerPanel>
	);
};
