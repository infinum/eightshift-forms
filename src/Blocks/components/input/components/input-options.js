/* global esFormsBlocksLocalization */

import React from 'react';
import { isArray } from 'lodash';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { TextControl, PanelBody, Button, Popover } from '@wordpress/components';
import {
	icons,
	getOption,
	checkAttr,
	getAttrKey,
	IconLabel,
	props,
	FancyDivider,
	SimpleVerticalSingleSelect,
	CustomSlider,
	CustomSelect
} from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../../components/field/components/field-options';
import { FieldOptionsAdvanced } from '../../field/components/field-options-advanced';
import manifest from './../manifest.json';

export const InputOptions = (attributes) => {
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

	const [showValidation, setShowValidation] = useState(false);

	let inputValidationPatternOptions = [];

	if (typeof esFormsBlocksLocalization !== 'undefined' && isArray(esFormsBlocksLocalization?.validationPatternsOptions)) {
		inputValidationPatternOptions = esFormsBlocksLocalization.validationPatternsOptions;
	}

	return (
		<>
			<PanelBody title={title}>
				<FieldOptions
					{...props('field', attributes)}
				/>

				{showInputPlaceholder &&
					<TextControl
						label={<IconLabel icon={icons.fieldPlaceholder} label={__('Placeholder', 'eightshift-forms')} />}
						help={__('Shown when the field is empty', 'eightshift-forms')}
						value={inputPlaceholder}
						onChange={(value) => setAttributes({ [getAttrKey('inputPlaceholder', attributes, manifest)]: value })}
					/>
				}

				{showInputType &&
					<CustomSelect
						label={<IconLabel icon={icons.fieldType} label={__('Input value kind', 'eightshift-forms')} />}
						value={inputType}
						options={getOption('inputType', attributes, manifest)}
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
						simpleValue
						isClearable={false}
						isSearchable={false}
					/>
				}

				{showInputAdvancedOptions &&
					<>
						<FancyDivider label={__('Advanced', 'eightshift-forms')} />

						{showInputName &&
							<TextControl
								label={<IconLabel icon={icons.fieldName} label={__('Name', 'eightshift-forms')} />}
								help={__('Should be unique! Used to identify the field within form submission data. If not set, a random name will be generated.', 'eightshift-forms')}
								value={inputName}
								onChange={(value) => setAttributes({ [getAttrKey('inputName', attributes, manifest)]: value })}
							/>
						}

						{showInputValue &&
							<TextControl
								label={<IconLabel icon={icons.fieldValue} label={__('Initial value', 'eightshift-forms')} />}
								value={inputValue}
								onChange={(value) => setAttributes({ [getAttrKey('inputValue', attributes, manifest)]: value })}
							/>
						}

						<div className='es-h-spaced'>
							{showInputIsReadOnly &&
								<Button
									icon={icons.fieldReadonly}
									isPressed={inputIsReadOnly}
									onClick={() => setAttributes({ [getAttrKey('inputIsReadOnly', attributes, manifest)]: !inputIsReadOnly })}
								>
									{__('Read-only', 'eightshift-forms')}
								</Button>
							}

							{showInputIsDisabled &&

								<Button
									icon={icons.fieldDisabled}
									isPressed={inputIsDisabled}
									onClick={() => setAttributes({ [getAttrKey('inputIsDisabled', attributes, manifest)]: !inputIsDisabled })}
								>
									{__('Disabled', 'eightshift-forms')}
								</Button>
							}
						</div>
					</>
				}

				{showInputValidationOptions &&
					<>
						<FancyDivider label={__('Validation', 'eightshift-forms')} />

						<div className='es-h-spaced-wrap'>
							{showInputIsRequired &&
								<Button
									icon={icons.fieldRequired}
									isPressed={inputIsRequired}
									onClick={() => setAttributes({ [getAttrKey('inputIsRequired', attributes, manifest)]: !inputIsRequired })}
								>
									{__('Required', 'eightshift-forms')}
								</Button>
							}

							{(showInputValidationPattern && !inputIsUrl && !inputIsEmail) &&
								<Button
									icon={icons.regex}
									isPressed={inputValidationPattern?.length > 0}
									onClick={() => setShowValidation(true)}
								>
									{__('Pattern validation', 'eightshift-forms')}

									{showValidation &&
										<Popover noArrow={false} onClose={() => setShowValidation(false)}>
											<div className='es-popover-content'>
												<SimpleVerticalSingleSelect
													label={__('Validation pattern', 'eightshift-forms')}
													options={inputValidationPatternOptions.map(({ label, value }) => ({
														onClick: () => setAttributes({ [getAttrKey('inputValidationPattern', attributes, manifest)]: value }),
														label: label,
														isActive: inputValidationPattern === value,
													}))}
												/>
											</div>
										</Popover>
									}
								</Button>
							}
						</div>

						{(showInputMinLength || showInputMaxLength) &&
							<FancyDivider label={__('Entry length', 'eightshift-forms')} />
						}

						{showInputMinLength &&
							<>
								<CustomSlider
									label={<IconLabel icon={icons.rangeMin} label={__('Smallest allowed length', 'eightshift-forms')} />}
									value={inputMinLength ?? 0}
									onChange={(value) => setAttributes({ [getAttrKey('inputMinLength', attributes, manifest)]: value })}
									min={options.inputMinLength.min}
									step={options.inputMinLength.step}
									hasValueDisplay
									rightAddition={
										<Button
											label={__('Reset', 'eightshift-forms')}
											icon={icons.rotateLeft}
											onClick={() => setAttributes({ [getAttrKey('inputMinLength', attributes, manifest)]: undefined })}
											isSmall
											className='es-small-square-icon-button'
										/>
									}
									valueDisplayElement={(<span className='es-custom-slider-current-value'>{inputMinLength ? parseInt(inputMinLength) : '--'}</span>)}
								/>
							</>
						}

						{showInputMaxLength &&
							<CustomSlider
								label={<IconLabel icon={icons.rangeMax} label={__('Largest allowed length', 'eightshift-forms')} />}
								value={inputMaxLength ?? 0}
								onChange={(value) => setAttributes({ [getAttrKey('inputMaxLength', attributes, manifest)]: value })}
								min={options.inputMaxLength.min}
								step={options.inputMaxLength.step}
								hasValueDisplay
								rightAddition={
									<Button
										label={__('Reset', 'eightshift-forms')}
										icon={icons.rotateLeft}
										onClick={() => setAttributes({ [getAttrKey('inputMaxLength', attributes, manifest)]: undefined })}
										isSmall
										className='es-small-square-icon-button'
									/>
								}
								valueDisplayElement={(<span className='es-custom-slider-current-value'>{inputMaxLength ? parseInt(inputMaxLength) : '--'}</span>)}
							/>
						}

						{inputType === 'number' &&
							<FancyDivider label={__('Number entry', 'eightshift-forms')} />
						}

						{(inputType === 'number' && showInputMin) &&
							<>
								<CustomSlider
									label={<IconLabel icon={icons.rangeMin} label={__('Smallest allowed number', 'eightshift-forms')} />}
									value={inputMin ?? 0}
									onChange={(value) => setAttributes({ [getAttrKey('inputMin', attributes, manifest)]: value })}
									min={options.inputMin.min}
									step={options.inputMin.step}
									hasValueDisplay
									rightAddition={
										<Button
											label={__('Reset', 'eightshift-forms')}
											icon={icons.rotateLeft}
											onClick={() => setAttributes({ [getAttrKey('inputMin', attributes, manifest)]: undefined })}
											isSmall
											className='es-small-square-icon-button'
										/>
									}
									valueDisplayElement={(<span className='es-custom-slider-current-value'>{inputMin ? parseInt(inputMin) : '--'}</span>)}
								/>
							</>
						}

						{(inputType === 'number' && showInputMax) &&
							<CustomSlider
								label={<IconLabel icon={icons.rangeMax} label={__('Largest allowed number', 'eightshift-forms')} />}
								value={inputMax ?? 0}
								onChange={(value) => setAttributes({ [getAttrKey('inputMax', attributes, manifest)]: value })}
								min={options.inputMax.min}
								step={options.inputMax.step}
								hasValueDisplay
								rightAddition={
									<Button
										label={__('Reset', 'eightshift-forms')}
										icon={icons.rotateLeft}
										onClick={() => setAttributes({ [getAttrKey('inputMax', attributes, manifest)]: undefined })}
										isSmall
										className='es-small-square-icon-button'
									/>
								}
								valueDisplayElement={(<span className='es-custom-slider-current-value'>{inputMax ? parseInt(inputMax) : '--'}</span>)}
							/>
						}

						{(inputType === 'number' && showInputStep) &&
							<CustomSlider
								label={<IconLabel icon={icons.step} label={__('Increment step', 'eightshift-forms')} />}
								value={inputStep ?? 1}
								onChange={(value) => setAttributes({ [getAttrKey('inputStep', attributes, manifest)]: value })}
								min={options.inputStep.min}
								step={options.inputStep.step}
								hasValueDisplay
								rightAddition={
									<Button
										label={__('Reset', 'eightshift-forms')}
										icon={icons.rotateLeft}
										onClick={() => setAttributes({ [getAttrKey('inputStep', attributes, manifest)]: undefined })}
										isSmall
										className='es-small-square-icon-button'
									/>
								}
								valueDisplayElement={(<span className='es-custom-slider-current-value'>{inputStep ? parseInt(inputStep) : '--'}</span>)}
							/>
						}
					</>
				}

				{showInputAdvancedOptions &&
					<>
						<FancyDivider label={__('Tracking', 'eightshift-forms')} />

						{showInputTracking &&
							<TextControl
								label={<IconLabel icon={icons.code} label={__('GTM tracking code', 'eightshift-forms')} />}
								value={inputTracking}
								onChange={(value) => setAttributes({ [getAttrKey('inputTracking', attributes, manifest)]: value })}
							/>
						}
					</>
				}
			</PanelBody>

			<FieldOptionsAdvanced
				{...props('field', attributes)}
			/>
		</>
	);
};
