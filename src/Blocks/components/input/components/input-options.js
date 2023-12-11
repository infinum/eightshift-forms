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

	let inputValidationPatternOptions = [];

	if (typeof esFormsLocalization !== 'undefined' && isArray(esFormsLocalization?.validationPatternsOptions)) {
		inputValidationPatternOptions = esFormsLocalization.validationPatternsOptions;
	}

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

							if (value === 'number') {
								setAttributes({ [getAttrKey('inputIsNumber', attributes, manifest)]: true });
							}

							if (value === 'url') {
								setAttributes({ [getAttrKey('inputIsUrl', attributes, manifest)]: true });
							}
						}}
						additionalSelectClasses='es-w-32'
						simpleValue
						inlineLabel
						noSearch
					/>
				}
			</Section>

			<FieldOptions
				{...props('field', attributes, {
					fieldDisabledOptions: inputDisabledOptions,
				})}
				showFieldHideLabel={false}
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

				{(showInputMinLength || showInputMaxLength) &&
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
										step={options.inputMinLength.step}
										disabled={isOptionDisabled(getAttrKey('inputMinLength', attributes, manifest), inputDisabledOptions)}
										placeholder='–'
										fixedWidth={4}
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
										step={options.inputMaxLength.step}
										disabled={isOptionDisabled(getAttrKey('inputMaxLength', attributes, manifest), inputDisabledOptions)}
										placeholder='–'
										fixedWidth={4}
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

				{inputType === 'number' && (showInputMin || showInputMax) &&
					<Control
						icon={icons.range}
						label={__('Value range', 'eightshift-forms')}
						additionalLabelClasses='es-mb-0!'
					>
						<div className='es-h-spaced es-gap-5!'>
							{showInputMin &&
								<div className='es-display-flex es-items-end es-gap-2'>
									<NumberPicker
										label={__('Min', 'eightshift-forms')}
										value={inputMin}
										onChange={(value) => setAttributes({ [getAttrKey('inputMin', attributes, manifest)]: value })}
										min={options.inputMin.min}
										step={options.inputMin.step}
										disabled={isOptionDisabled(getAttrKey('inputMin', attributes, manifest), inputDisabledOptions)}
										placeholder='–'
										fixedWidth={4}
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
										onChange={(value) => setAttributes({ [getAttrKey('inputMax', attributes, manifest)]: value })}
										min={options.inputMax.min}
										step={options.inputMax.step}
										disabled={isOptionDisabled(getAttrKey('inputMax', attributes, manifest), inputDisabledOptions)}
										placeholder='–'
										fixedWidth={4}
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

				{inputType === 'number' && showInputStep &&
					<Control label={__('Increment step', 'eightshift-forms')} additionalLabelClasses='es-mb-0!'>
						<div className='es-display-flex es-items-end es-gap-2'>
							<NumberPicker
								value={inputStep}
								onChange={(value) => setAttributes({ [getAttrKey('inputStep', attributes, manifest)]: value })}
								min={options.inputStep.min}
								step={options.inputStep.step}
								disabled={isOptionDisabled(getAttrKey('inputStep', attributes, manifest), inputDisabledOptions)}
								fixedWidth={4}
								noBottomSpacing
								placeholder='1'
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
