/* global esFormsLocalization */

import React from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { getOption, checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import {
	Select,
	NumberPicker,
	ContainerPanel,
	InputField,
	Toggle,
	HStack,
	Spacer,
	Button,
} from '@eightshift/ui-components';
import { icons } from '@eightshift/ui-components/icons';
import {
	FieldOptions,
	FieldOptionsMore,
	FieldOptionsLayout,
	FieldOptionsVisibility,
} from '../../field/components/field-options';
import { isOptionDisabled, NameField } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import manifest from '../manifest.json';

export const InputOptions = (attributes) => {
	const { options } = manifest;

	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const inputName = checkAttr('inputName', attributes, manifest);
	const inputValue = checkAttr('inputValue', attributes, manifest);
	const inputPlaceholder = checkAttr('inputPlaceholder', attributes, manifest);
	const inputType = checkAttr('inputType', attributes, manifest);
	const inputIsDisabled = checkAttr('inputIsDisabled', attributes, manifest);
	const inputIsReadOnly = checkAttr('inputIsReadOnly', attributes, manifest);
	const inputIsRequired = checkAttr('inputIsRequired', attributes, manifest);
	const inputTracking = checkAttr('inputTracking', attributes, manifest);
	const inputIsEmail = checkAttr('inputIsEmail', attributes, manifest);
	const inputIsUrl = checkAttr('inputIsUrl', attributes, manifest);
	const inputValidationPattern = checkAttr('inputValidationPattern', attributes, manifest);
	const inputMinLength = checkAttr('inputMinLength', attributes, manifest);
	const inputMaxLength = checkAttr('inputMaxLength', attributes, manifest);
	const inputMin = checkAttr('inputMin', attributes, manifest);
	const inputMax = checkAttr('inputMax', attributes, manifest);
	const inputStep = checkAttr('inputStep', attributes, manifest);
	const inputDisabledOptions = checkAttr('inputDisabledOptions', attributes, manifest);
	const inputUseLabelAsPlaceholder = checkAttr('inputUseLabelAsPlaceholder', attributes, manifest);
	const inputRangeShowMin = checkAttr('inputRangeShowMin', attributes, manifest);
	const inputRangeShowMinPrefix = checkAttr('inputRangeShowMinPrefix', attributes, manifest);
	const inputRangeShowMinSuffix = checkAttr('inputRangeShowMinSuffix', attributes, manifest);
	const inputRangeShowMax = checkAttr('inputRangeShowMax', attributes, manifest);
	const inputRangeShowMaxPrefix = checkAttr('inputRangeShowMaxPrefix', attributes, manifest);
	const inputRangeShowMaxSuffix = checkAttr('inputRangeShowMaxSuffix', attributes, manifest);
	const inputRangeShowCurrent = checkAttr('inputRangeShowCurrent', attributes, manifest);
	const inputRangeShowCurrentPrefix = checkAttr('inputRangeShowCurrentPrefix', attributes, manifest);
	const inputRangeShowCurrentSuffix = checkAttr('inputRangeShowCurrentSuffix', attributes, manifest);
	const inputRangeUseCustomField = checkAttr('inputRangeUseCustomField', attributes, manifest);

	let inputValidationPatternOptions = [];

	if (typeof esFormsLocalization !== 'undefined') {
		inputValidationPatternOptions = esFormsLocalization.validationPatternsOptions;
	}

	// Output number to 2 decimal places if it's a float, otherwise output to fixed number.
	const formatNumber = (number) => Number(Number.isInteger(number) ? number.toString() : number.toFixed(2));

	return (
		<ContainerPanel>
			<Spacer
				border
				icon={icons.options}
				text={__('General', 'eightshift-forms')}
			/>

			<NameField
				value={inputName}
				attribute={getAttrKey('inputName', attributes, manifest)}
				disabledOptions={inputDisabledOptions}
				setAttributes={setAttributes}
				type='input'
				isChanged={isNameChanged}
				setIsChanged={setIsNameChanged}
			/>

			<Select
				icon={icons.optionListAlt}
				label={__('Type', 'eightshift-forms')}
				value={inputType}
				options={getOption('inputType', attributes, manifest)}
				disabled={isOptionDisabled(getAttrKey('inputType', attributes, manifest), inputDisabledOptions)}
				onChange={(value) => {
					setAttributes({ [getAttrKey('inputType', attributes, manifest)]: value });

					setAttributes({ [getAttrKey('inputIsEmail', attributes, manifest)]: false });
					setAttributes({ [getAttrKey('inputIsNumber', attributes, manifest)]: false });
					setAttributes({ [getAttrKey('inputIsUrl', attributes, manifest)]: false });

					if (value === 'email') {
						setAttributes({ [getAttrKey('inputIsEmail', attributes, manifest)]: true });
					}

					if (value === 'number' || value === 'range') {
						setAttributes({ [getAttrKey('inputIsNumber', attributes, manifest)]: true });
					}

					if (value === 'url') {
						setAttributes({ [getAttrKey('inputIsUrl', attributes, manifest)]: true });
					}

					setAttributes({ [getAttrKey('inputRangeUseCustomField', attributes, manifest)]: undefined });
				}}
				simpleValue
				noSearch
			/>

			{inputType === 'range' && (
				<Toggle
					icon={icons.fieldPlaceholder}
					label={__('Show custom input field', 'eightshift-forms')}
					checked={inputRangeUseCustomField}
					onChange={(value) => setAttributes({ [getAttrKey('inputRangeUseCustomField', attributes, manifest)]: value })}
				/>
			)}

			<FieldOptions
				{...props('field', attributes, {
					fieldDisabledOptions: inputDisabledOptions,
				})}
			/>

			<>
				<Toggle
					icon={icons.fieldPlaceholder}
					label={__('Use label as placeholder', 'eightshift-forms')}
					checked={inputUseLabelAsPlaceholder}
					onChange={(value) => {
						setAttributes({ [getAttrKey('inputPlaceholder', attributes, manifest)]: undefined });
						setAttributes({ [getAttrKey('inputUseLabelAsPlaceholder', attributes, manifest)]: value });
					}}
				/>
				{!inputUseLabelAsPlaceholder && (
					<InputField
						placeholder={__('Enter placeholder', 'eightshift-forms')}
						help={__('Shown when the field is empty', 'eightshift-forms')}
						value={inputPlaceholder}
						onChange={(value) => setAttributes({ [getAttrKey('inputPlaceholder', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('inputPlaceholder', attributes, manifest), inputDisabledOptions)}
					/>
				)}
			</>

			<FieldOptionsLayout
				{...props('field', attributes, {
					fieldDisabledOptions: inputDisabledOptions,
				})}
			/>

			<Spacer
				border
				icon={icons.tools}
				text={__('Advanced', 'eightshift-forms')}
			/>

			<InputField
				icon={icons.titleGeneric}
				label={__('Initial value', 'eightshift-forms')}
				placeholder={__('Enter initial value', 'eightshift-forms')}
				value={inputValue}
				onChange={(value) => setAttributes({ [getAttrKey('inputValue', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('inputValue', attributes, manifest), inputDisabledOptions)}
			/>

			<FieldOptionsVisibility
				{...props('field', attributes, {
					fieldDisabledOptions: inputDisabledOptions,
				})}
			/>

			<Toggle
				icon={icons.readOnly}
				label={__('Read-only', 'eightshift-forms')}
				checked={inputIsReadOnly}
				onChange={(value) => setAttributes({ [getAttrKey('inputIsReadOnly', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('inputIsReadOnly', attributes, manifest), inputDisabledOptions)}
			/>

			<Toggle
				icon={icons.cursorDisabled}
				label={__('Disabled', 'eightshift-forms')}
				checked={inputIsDisabled}
				onChange={(value) => setAttributes({ [getAttrKey('inputIsDisabled', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('inputIsDisabled', attributes, manifest), inputDisabledOptions)}
			/>

			<Spacer
				border
				icon={icons.checks}
				text={__('Validation', 'eightshift-forms')}
			/>
			<Toggle
				icon={icons.fieldRequired}
				label={__('Required', 'eightshift-forms')}
				checked={inputIsRequired}
				onChange={(value) => setAttributes({ [getAttrKey('inputIsRequired', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('inputIsRequired', attributes, manifest), inputDisabledOptions)}
			/>

			{!inputIsUrl && !inputIsEmail && (
				<Select
					icon={icons.regex}
					label={__('Match pattern', 'eightshift-forms')}
					options={inputValidationPatternOptions}
					value={inputValidationPattern}
					onChange={(value) => setAttributes({ [getAttrKey('inputValidationPattern', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('inputValidationPattern', attributes, manifest), inputDisabledOptions)}
					placeholder='–'
					clearable
				/>
			)}

			{!['number', 'range'].includes(inputType) && (
				<HStack>
					<NumberPicker
						aria-label={__('Min length', 'eightshift-forms')}
						value={inputMinLength}
						onChange={(value) => setAttributes({ [getAttrKey('inputMinLength', attributes, manifest)]: value })}
						min={options.inputMinLength.min}
						max={options.inputMinLength.max}
						step={options.inputMinLength.step}
						disabled={isOptionDisabled(getAttrKey('inputMinLength', attributes, manifest), inputDisabledOptions)}
						placeholder='–'
						prefix={__('Min length', 'eightshift-forms')}
					>
						<Button
							icon={icons.resetToZero}
							tooltip={__('Reset', 'eightshift-forms')}
							onClick={() => setAttributes({ [getAttrKey('inputMinLength', attributes, manifest)]: undefined })}
							disabled={inputMinLength === 0}
							type='ghost'
							slot={null}
						>
							{__('x', 'eightshift-forms')}
						</Button>
					</NumberPicker>

					<NumberPicker
						aria-label={__('Max length', 'eightshift-forms')}
						value={inputMaxLength}
						onChange={(value) => setAttributes({ [getAttrKey('inputMaxLength', attributes, manifest)]: value })}
						min={options.inputMaxLength.min}
						max={options.inputMaxLength.max}
						step={options.inputMaxLength.step}
						disabled={isOptionDisabled(getAttrKey('inputMaxLength', attributes, manifest), inputDisabledOptions)}
						placeholder='–'
						prefix={__('Max length', 'eightshift-forms')}
					>
						<Button
							icon={icons.resetToZero}
							tooltip={__('Reset', 'eightshift-forms')}
							onClick={() => setAttributes({ [getAttrKey('inputMaxLength', attributes, manifest)]: undefined })}
							disabled={inputMaxLength === 0}
							type='ghost'
							slot={null}
						>
							{__('x', 'eightshift-forms')}
						</Button>
					</NumberPicker>
				</HStack>
			)}

			{(inputType === 'number' || inputType === 'range') && (
				<>
					<HStack>
						<NumberPicker
							aria-label={__('Min', 'eightshift-forms')}
							value={inputMin}
							onChange={(value) =>
								setAttributes({ [getAttrKey('inputMin', attributes, manifest)]: formatNumber(value) })
							}
							min={options.inputMin.min}
							max={options.inputMin.max}
							step={options.inputMin.step}
							disabled={isOptionDisabled(getAttrKey('inputMin', attributes, manifest), inputDisabledOptions)}
							placeholder='–'
							prefix={__('Min', 'eightshift-forms')}
						>
							<Button
								icon={icons.resetToZero}
								tooltip={__('Reset', 'eightshift-forms')}
								onClick={() => setAttributes({ [getAttrKey('inputMin', attributes, manifest)]: undefined })}
								disabled={inputMin === 0}
								type='ghost'
								slot={null}
							>
								{__('x', 'eightshift-forms')}
							</Button>
						</NumberPicker>

						<NumberPicker
							aria-label={__('Max', 'eightshift-forms')}
							value={inputMax}
							onChange={(value) =>
								setAttributes({ [getAttrKey('inputMax', attributes, manifest)]: formatNumber(value) })
							}
							min={options.inputMax.min}
							max={options.inputMax.max}
							step={options.inputMax.step}
							disabled={isOptionDisabled(getAttrKey('inputMax', attributes, manifest), inputDisabledOptions)}
							placeholder='–'
							prefix={__('Max', 'eightshift-forms')}
						>
							<Button
								icon={icons.resetToZero}
								tooltip={__('Reset', 'eightshift-forms')}
								onClick={() => setAttributes({ [getAttrKey('inputMax', attributes, manifest)]: undefined })}
								disabled={inputMax === 0}
								type='ghost'
								slot={null}
							>
								{__('x', 'eightshift-forms')}
							</Button>
						</NumberPicker>
						<NumberPicker
							value={inputStep}
							onChange={(value) => setAttributes({ [getAttrKey('inputStep', attributes, manifest)]: value })}
							min={options.inputStep.min}
							max={options.inputStep.max}
							step={options.inputStep.step}
							disabled={isOptionDisabled(getAttrKey('inputStep', attributes, manifest), inputDisabledOptions)}
							prefix={__('Step', 'eightshift-forms')}
						>
							<Button
								icon={icons.resetToZero}
								tooltip={__('Reset', 'eightshift-forms')}
								onClick={() => setAttributes({ [getAttrKey('inputStep', attributes, manifest)]: undefined })}
								disabled={inputStep === 0}
								type='ghost'
								slot={null}
							>
								{__('x', 'eightshift-forms')}
							</Button>
						</NumberPicker>
					</HStack>

					{inputType === 'range' && (
						<>
							<Toggle
								label={__('Show min value', 'eightshift-forms')}
								checked={inputRangeShowMin}
								onChange={(value) => {
									setAttributes({ [getAttrKey('inputRangeShowMin', attributes, manifest)]: value });

									if (!value) {
										setAttributes({ [getAttrKey('inputRangeShowMinPrefix', attributes, manifest)]: undefined });
										setAttributes({ [getAttrKey('inputRangeShowMinSuffix', attributes, manifest)]: undefined });
									}
								}}
								disabled={isOptionDisabled(getAttrKey('inputRangeShowMin', attributes, manifest), inputDisabledOptions)}
							>
								<HStack>
									<InputField
										label={__('Min prefix', 'eightshift-forms')}
										value={inputRangeShowMinPrefix}
										onChange={(value) =>
											setAttributes({ [getAttrKey('inputRangeShowMinPrefix', attributes, manifest)]: value })
										}
										disabled={isOptionDisabled(
											getAttrKey('inputRangeShowMinPrefix', attributes, manifest),
											inputDisabledOptions,
										)}
									/>
									<InputField
										label={__('Min suffix', 'eightshift-forms')}
										value={inputRangeShowMinSuffix}
										onChange={(value) =>
											setAttributes({ [getAttrKey('inputRangeShowMinSuffix', attributes, manifest)]: value })
										}
										disabled={isOptionDisabled(
											getAttrKey('inputRangeShowMinSuffix', attributes, manifest),
											inputDisabledOptions,
										)}
									/>
								</HStack>
							</Toggle>
							<Toggle
								label={__('Show current value', 'eightshift-forms')}
								checked={inputRangeShowCurrent}
								onChange={(value) => {
									setAttributes({ [getAttrKey('inputRangeShowCurrent', attributes, manifest)]: value });

									if (!value) {
										setAttributes({ [getAttrKey('inputRangeShowCurrentPrefix', attributes, manifest)]: undefined });
										setAttributes({ [getAttrKey('inputRangeShowCurrentSuffix', attributes, manifest)]: undefined });
									}
								}}
								disabled={isOptionDisabled(
									getAttrKey('inputRangeShowCurrent', attributes, manifest),
									inputDisabledOptions,
								)}
							>
								<HStack>
									<InputField
										label={__('Current prefix', 'eightshift-forms')}
										value={inputRangeShowCurrentPrefix}
										onChange={(value) =>
											setAttributes({ [getAttrKey('inputRangeShowCurrentPrefix', attributes, manifest)]: value })
										}
										disabled={isOptionDisabled(
											getAttrKey('inputRangeShowCurrentPrefix', attributes, manifest),
											inputDisabledOptions,
										)}
									/>
									<InputField
										label={__('Current suffix', 'eightshift-forms')}
										value={inputRangeShowCurrentSuffix}
										onChange={(value) =>
											setAttributes({ [getAttrKey('inputRangeShowCurrentSuffix', attributes, manifest)]: value })
										}
										disabled={isOptionDisabled(
											getAttrKey('inputRangeShowCurrentSuffix', attributes, manifest),
											inputDisabledOptions,
										)}
									/>
								</HStack>
							</Toggle>
							<Toggle
								label={__('Show max value', 'eightshift-forms')}
								checked={inputRangeShowMax}
								onChange={(value) => {
									setAttributes({ [getAttrKey('inputRangeShowMax', attributes, manifest)]: value });

									if (!value) {
										setAttributes({ [getAttrKey('inputRangeShowMaxPrefix', attributes, manifest)]: undefined });
										setAttributes({ [getAttrKey('inputRangeShowMaxSuffix', attributes, manifest)]: undefined });
									}
								}}
								disabled={isOptionDisabled(getAttrKey('inputRangeShowMax', attributes, manifest), inputDisabledOptions)}
							>
								<HStack>
									<InputField
										label={__('Max prefix', 'eightshift-forms')}
										value={inputRangeShowMaxPrefix}
										onChange={(value) =>
											setAttributes({ [getAttrKey('inputRangeShowMaxPrefix', attributes, manifest)]: value })
										}
										disabled={isOptionDisabled(
											getAttrKey('inputRangeShowMaxPrefix', attributes, manifest),
											inputDisabledOptions,
										)}
									/>
									<InputField
										label={__('Max suffix', 'eightshift-forms')}
										value={inputRangeShowMaxSuffix}
										onChange={(value) =>
											setAttributes({ [getAttrKey('inputRangeShowMaxSuffix', attributes, manifest)]: value })
										}
										disabled={isOptionDisabled(
											getAttrKey('inputRangeShowMaxSuffix', attributes, manifest),
											inputDisabledOptions,
										)}
									/>
								</HStack>
							</Toggle>
						</>
					)}
				</>
			)}

			<Spacer
				border
				icon={icons.alignHorizontalVertical}
				text={__('Tracking', 'eightshift-forms')}
			/>

			<InputField
				icon={icons.googleTagManager}
				label={__('GTM tracking code', 'eightshift-forms')}
				value={inputTracking}
				onChange={(value) => setAttributes({ [getAttrKey('inputTracking', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('inputTracking', attributes, manifest), inputDisabledOptions)}
			/>

			<FieldOptionsMore
				{...props('field', attributes, {
					fieldDisabledOptions: inputDisabledOptions,
				})}
			/>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: inputName,
					conditionalTagsIsHidden: checkAttr('inputFieldHidden', attributes, manifest),
				})}
			/>
		</ContainerPanel>
	);
};
