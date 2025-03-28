/* global esFormsLocalization */

import React from 'react';
import { useState } from '@wordpress/element';
import { select } from '@wordpress/data';
import { isArray } from 'lodash';
import { __ } from '@wordpress/i18n';
import { TextControl, PanelBody, Button } from '@wordpress/components';
import {
	icons,
	getOption,
	checkAttr,
	getAttrKey,
	IconLabel,
	props,
	Select,
	Section,
	NumberPicker,
	IconToggle,
	UseToggle,
	Control,
	STORE_NAME,
} from '@eightshift/frontend-libs/scripts';
import { FieldOptions, FieldOptionsMore, FieldOptionsLayout, FieldOptionsVisibility } from '../../field/components/field-options';
import { isOptionDisabled, NameField } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';

export const InputOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('input');

	const {
		options,
	} = manifest;

	const {
		setAttributes,

		showInputName = true,
		showInputValue = true,
		showInputAdvancedOptions = true,
		showInputPlaceholder = true,
		showInputType = true,
		showInputValidationOptions = true,
		showInputIsDisabled = true,
		showInputIsReadOnly = true,
		showInputIsRequired = true,
		showInputTracking = true,
		showInputValidationPattern = true,
		showInputMinLength = true,
		showInputMaxLength = true,
		showInputMin = true,
		showInputMax = true,
		showInputStep = true,

		title = __('Input', 'eightshift-forms'),
	} = attributes;

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

	if (typeof esFormsLocalization !== 'undefined' && isArray(esFormsLocalization?.validationPatternsOptions)) {
		inputValidationPatternOptions = esFormsLocalization.validationPatternsOptions;
	}

	// Output number to 2 decimal places if it's a float, otherwise output to fixed number.
	const formatNumber = (number) => Number((Number.isInteger(number) ? number.toString() : number.toFixed(2)));

	return (
		<PanelBody title={title}>
			<Section showIf={showInputPlaceholder || showInputType || showInputName} icon={icons.options} label={__('General', 'eightshift-forms')}>
				<NameField
					value={inputName}
					attribute={getAttrKey('inputName', attributes, manifest)}
					disabledOptions={inputDisabledOptions}
					setAttributes={setAttributes}
					show={showInputName}
					type='input'
					isChanged={isNameChanged}
					setIsChanged={setIsNameChanged}
				/>

				{showInputType &&
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
						additionalSelectClasses='es-w-32'
						simpleValue
						inlineLabel
						noSearch
						closeMenuAfterSelect
					/>
				}

				{inputType === 'range' && (
					<IconToggle
						icon={icons.fieldPlaceholder}
						label={__('Show custom input field', 'eightshift-forms')}
						checked={inputRangeUseCustomField}
						onChange={(value) => setAttributes({ [getAttrKey('inputRangeUseCustomField', attributes, manifest)]: value })}
					/>
				)}
			</Section>

			<FieldOptions
				{...props('field', attributes, {
					fieldDisabledOptions: inputDisabledOptions,
				})}
			/>

			<Section showIf={showInputPlaceholder} icon={icons.fieldPlaceholder} label={__('Placeholder', 'eightshift-forms')}>
				{!inputUseLabelAsPlaceholder &&
					<TextControl
						help={__('Shown when the field is empty', 'eightshift-forms')}
						value={inputPlaceholder}
						onChange={(value) => setAttributes({ [getAttrKey('inputPlaceholder', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('inputPlaceholder', attributes, manifest), inputDisabledOptions)}
						className='es-no-field-spacing'
					/>
				}
				<IconToggle
					icon={icons.fieldPlaceholder}
					label={__('Use label as placeholder', 'eightshift-forms')}
					checked={inputUseLabelAsPlaceholder}
					onChange={(value) => {
						setAttributes({ [getAttrKey('inputPlaceholder', attributes, manifest)]: undefined });
						setAttributes({ [getAttrKey('inputUseLabelAsPlaceholder', attributes, manifest)]: value });
					}}
				/>
			</Section>

			<FieldOptionsLayout
				{...props('field', attributes, {
					fieldDisabledOptions: inputDisabledOptions,
				})}
			/>

			<Section showIf={showInputAdvancedOptions} icon={icons.tools} label={__('Advanced', 'eightshift-forms')}>
				{showInputValue &&
					<TextControl
						label={<IconLabel icon={icons.titleGeneric} label={__('Initial value', 'eightshift-forms')} />}
						value={inputValue}
						onChange={(value) => setAttributes({ [getAttrKey('inputValue', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('inputValue', attributes, manifest), inputDisabledOptions)}
					/>
				}

				<FieldOptionsVisibility
					{...props('field', attributes, {
						fieldDisabledOptions: inputDisabledOptions,
					})}
				/>


				{showInputIsReadOnly &&
					<IconToggle
						icon={icons.readOnly}
						label={__('Read-only', 'eightshift-forms')}
						checked={inputIsReadOnly}
						onChange={(value) => setAttributes({ [getAttrKey('inputIsReadOnly', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('inputIsReadOnly', attributes, manifest), inputDisabledOptions)}
					/>
				}

				{showInputIsDisabled &&
					<IconToggle
						icon={icons.cursorDisabled}
						label={__('Disabled', 'eightshift-forms')}
						checked={inputIsDisabled}
						onChange={(value) => setAttributes({ [getAttrKey('inputIsDisabled', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('inputIsDisabled', attributes, manifest), inputDisabledOptions)}
						noBottomSpacing
					/>
				}
			</Section>

			<Section showIf={showInputValidationOptions} icon={icons.checks} label={__('Validation', 'eightshift-forms')}>
				{showInputIsRequired &&
					<IconToggle
						icon={icons.required}
						label={__('Required', 'eightshift-forms')}
						checked={inputIsRequired}
						onChange={(value) => setAttributes({ [getAttrKey('inputIsRequired', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('inputIsRequired', attributes, manifest), inputDisabledOptions)}
					/>
				}

				{(!['number', 'range'].includes(inputType) && (showInputMinLength || showInputMaxLength)) &&
					<Control
						icon={icons.textLength}
						label={__('Entry length', 'eightshift-forms')}
						additionalLabelClasses='es-mb-0!'
					>
						<div className='es-h-spaced es-gap-5!'>
							{showInputMinLength &&
								<div className='es-display-flex es-items-end es-gap-2'>
									<NumberPicker
										label={__('Min', 'eightshift-forms')}
										value={inputMinLength}
										onChange={(value) => setAttributes({ [getAttrKey('inputMinLength', attributes, manifest)]: value })}
										min={options.inputMinLength.min}
										max={options.inputMinLength.max}
										step={options.inputMinLength.step}
										disabled={isOptionDisabled(getAttrKey('inputMinLength', attributes, manifest), inputDisabledOptions)}
										placeholder='–'
										fixedWidth={5}
										noBottomSpacing
									/>

									{inputMinLength > 0 && !isOptionDisabled(getAttrKey('inputMinLength', attributes, manifest), inputDisabledOptions) &&
										<Button
											label={__('Disable', 'eightshift-forms')}
											icon={icons.clear}
											onClick={() => setAttributes({ [getAttrKey('inputMinLength', attributes, manifest)]: undefined })}
											className='es-button-square-32 es-button-icon-24'
											showTooltip
											isSmall
										/>
									}
								</div>
							}

							{showInputMaxLength &&
								<div className='es-display-flex es-items-end es-gap-2'>
									<NumberPicker
										label={__('Max', 'eightshift-forms')}
										value={inputMaxLength}
										onChange={(value) => setAttributes({ [getAttrKey('inputMaxLength', attributes, manifest)]: value })}
										min={options.inputMaxLength.min}
										max={options.inputMaxLength.max}
										step={options.inputMaxLength.step}
										disabled={isOptionDisabled(getAttrKey('inputMaxLength', attributes, manifest), inputDisabledOptions)}
										placeholder='–'
										fixedWidth={5}
										noBottomSpacing
									/>

									{inputMaxLength > 0 && !isOptionDisabled(getAttrKey('inputMaxLength', attributes, manifest), inputDisabledOptions) &&
										<Button
											label={__('Disable', 'eightshift-forms')}
											icon={icons.clear}
											onClick={() => setAttributes({ [getAttrKey('inputMaxLength', attributes, manifest)]: undefined })}
											className='es-button-square-32 es-button-icon-24'
											showTooltip
										/>
									}
								</div>
							}
						</div>
					</Control>
				}

				{((inputType === 'number' || inputType === 'range') && (showInputMin || showInputMax)) &&
					<Control
						icon={icons.range}
						label={__('Value range', 'eightshift-forms')}
						additionalLabelClasses='es-mb-0!'
					>
						{inputType === 'range' &&
							<>
								<UseToggle
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
									noBottomSpacing
								>
									<div className='es-h-center es-mb-5'>
										<TextControl
											label={<IconLabel label={__('Min prefix', 'eightshift-forms')} />}
											value={inputRangeShowMinPrefix}
											onChange={(value) => setAttributes({ [getAttrKey('inputRangeShowMinPrefix', attributes, manifest)]: value })}
											disabled={isOptionDisabled(getAttrKey('inputRangeShowMinPrefix', attributes, manifest), inputDisabledOptions)}
											className='es-no-field-spacing'
										/>
										<TextControl
											label={<IconLabel label={__('Min suffix', 'eightshift-forms')} />}
											value={inputRangeShowMinSuffix}
											onChange={(value) => setAttributes({ [getAttrKey('inputRangeShowMinSuffix', attributes, manifest)]: value })}
											disabled={isOptionDisabled(getAttrKey('inputRangeShowMinSuffix', attributes, manifest), inputDisabledOptions)}
											className='es-no-field-spacing'
										/>
									</div>
								</UseToggle>
								<UseToggle
									label={__('Show current value', 'eightshift-forms')}
									checked={inputRangeShowCurrent}
									onChange={(value) => {
										setAttributes({ [getAttrKey('inputRangeShowCurrent', attributes, manifest)]: value });

										if (!value) {
											setAttributes({ [getAttrKey('inputRangeShowCurrentPrefix', attributes, manifest)]: undefined });
											setAttributes({ [getAttrKey('inputRangeShowCurrentSuffix', attributes, manifest)]: undefined });
										}
									}}
									disabled={isOptionDisabled(getAttrKey('inputRangeShowCurrent', attributes, manifest), inputDisabledOptions)}
									noBottomSpacing
								>
									<div className='es-h-center es-mb-5'>
										<TextControl
											label={<IconLabel label={__('Current prefix', 'eightshift-forms')} />}
											value={inputRangeShowCurrentPrefix}
											onChange={(value) => setAttributes({ [getAttrKey('inputRangeShowCurrentPrefix', attributes, manifest)]: value })}
											disabled={isOptionDisabled(getAttrKey('inputRangeShowCurrentPrefix', attributes, manifest), inputDisabledOptions)}
											className='es-no-field-spacing'
										/>
										<TextControl
											label={<IconLabel label={__('Current suffix', 'eightshift-forms')} />}
											value={inputRangeShowCurrentSuffix}
											onChange={(value) => setAttributes({ [getAttrKey('inputRangeShowCurrentSuffix', attributes, manifest)]: value })}
											disabled={isOptionDisabled(getAttrKey('inputRangeShowCurrentSuffix', attributes, manifest), inputDisabledOptions)}
											className='es-no-field-spacing'
										/>
									</div>
								</UseToggle>
								<UseToggle
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
									<div className='es-h-center es-mb-5'>
										<TextControl
											label={<IconLabel label={__('Max prefix', 'eightshift-forms')} />}
											value={inputRangeShowMaxPrefix}
											onChange={(value) => setAttributes({ [getAttrKey('inputRangeShowMaxPrefix', attributes, manifest)]: value })}
											disabled={isOptionDisabled(getAttrKey('inputRangeShowMaxPrefix', attributes, manifest), inputDisabledOptions)}
											className='es-no-field-spacing'
										/>
										<TextControl
											label={<IconLabel label={__('Max suffix', 'eightshift-forms')} />}
											value={inputRangeShowMaxSuffix}
											onChange={(value) => setAttributes({ [getAttrKey('inputRangeShowMaxSuffix', attributes, manifest)]: value })}
											disabled={isOptionDisabled(getAttrKey('inputRangeShowMaxSuffix', attributes, manifest), inputDisabledOptions)}
											className='es-no-field-spacing'
										/>
									</div>
								</UseToggle>
							</>
						}

						<div className='es-h-spaced es-gap-5!'>
							{showInputMin &&
								<div className='es-display-flex es-items-end es-gap-2'>
									<NumberPicker
										label={__('Min', 'eightshift-forms')}
										value={inputMin}
										onChange={(value) => setAttributes({ [getAttrKey('inputMin', attributes, manifest)]: formatNumber(value) })}
										min={options.inputMin.min}
										max={options.inputMin.max}
										step={options.inputMin.step}
										disabled={isOptionDisabled(getAttrKey('inputMin', attributes, manifest), inputDisabledOptions)}
										placeholder='–'
										fixedWidth={5}
										noBottomSpacing
									/>

									{inputMin > 0 && !isOptionDisabled(getAttrKey('inputMin', attributes, manifest), inputDisabledOptions) &&
										<Button
											label={__('Disable', 'eightshift-forms')}
											icon={icons.clear}
											onClick={() => setAttributes({ [getAttrKey('inputMin', attributes, manifest)]: undefined })}
											className='es-button-square-32 es-button-icon-24'
											showTooltip
										/>
									}
								</div>
							}

							{showInputMax &&
								<div className='es-display-flex es-items-end es-gap-2'>
									<NumberPicker
										label={__('Max', 'eightshift-forms')}
										value={inputMax}
										onChange={(value) => setAttributes({ [getAttrKey('inputMax', attributes, manifest)]: formatNumber(value) })}
										min={options.inputMax.min}
										max={options.inputMax.max}
										step={options.inputMax.step}
										disabled={isOptionDisabled(getAttrKey('inputMax', attributes, manifest), inputDisabledOptions)}
										placeholder='–'
										fixedWidth={5}
										noBottomSpacing
									/>

									{inputMax > 0 && !isOptionDisabled(getAttrKey('inputMax', attributes, manifest), inputDisabledOptions) &&
										<Button
											label={__('Disable', 'eightshift-forms')}
											icon={icons.clear}
											onClick={() => setAttributes({ [getAttrKey('inputMax', attributes, manifest)]: undefined })}
											className='es-button-square-32 es-button-icon-24'
											showTooltip
										/>
									}
								</div>
							}
						</div>
					</Control>
				}

				{(inputType === 'number' || inputType === 'range') && showInputStep &&
					<Control label={__('Increment step', 'eightshift-forms')} additionalLabelClasses='es-mb-0!'>
						<div className='es-display-flex es-items-end es-gap-2'>
							<NumberPicker
								value={inputStep}
								onChange={(value) => setAttributes({ [getAttrKey('inputStep', attributes, manifest)]: value })}
								min={options.inputStep.min}
								max={options.inputStep.max}
								step={options.inputStep.step}
								disabled={isOptionDisabled(getAttrKey('inputStep', attributes, manifest), inputDisabledOptions)}
								fixedWidth={5}
								noBottomSpacing
							/>

							{inputStep > 0 && !isOptionDisabled(getAttrKey('inputStep', attributes, manifest), inputDisabledOptions) &&
								<Button
									label={__('Disable', 'eightshift-forms')}
									icon={icons.clear}
									onClick={() => setAttributes({ [getAttrKey('inputStep', attributes, manifest)]: undefined })}
									className='es-button-square-32 es-button-icon-24'
									showTooltip
								/>
							}
						</div>
					</Control>
				}

				{showInputValidationPattern && !inputIsUrl && !inputIsEmail &&
					<Select
						icon={icons.regex}
						label={__('Match pattern', 'eightshift-forms')}
						options={inputValidationPatternOptions}
						value={inputValidationPattern}
						onChange={(value) => setAttributes({ [getAttrKey('inputValidationPattern', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('inputValidationPattern', attributes, manifest), inputDisabledOptions)}
						placeholder='–'
						additionalSelectClasses='es-w-32'
						noBottomSpacing
						inlineLabel
						clearable
					/>
				}
			</Section>

			<Section showIf={showInputAdvancedOptions} icon={icons.alignHorizontalVertical} label={__('Tracking', 'eightshift-forms')} collapsable>
				{showInputTracking &&
					<TextControl
						label={<IconLabel icon={icons.googleTagManager} label={__('GTM tracking code', 'eightshift-forms')} />}
						value={inputTracking}
						onChange={(value) => setAttributes({ [getAttrKey('inputTracking', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('inputTracking', attributes, manifest), inputDisabledOptions)}
						className='es-no-field-spacing'
					/>
				}
			</Section>

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
		</PanelBody>
	);
};
