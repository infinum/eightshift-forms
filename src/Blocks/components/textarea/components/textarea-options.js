/* global esFormsLocalization */

import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { isArray } from 'lodash';
import { TextControl, PanelBody, Button, Popover } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	props,
	SimpleVerticalSingleSelect,
	FancyDivider,
	CustomSlider,
} from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../../components/field/components/field-options';
import { FieldOptionsAdvanced } from '../../field/components/field-options-advanced';
import manifest from '../manifest.json';
import { isOptionDisabled, MissingName } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';

export const TextareaOptions = (attributes) => {
	const {
		options,
	} = manifest;

	const {
		setAttributes,

		showTextareaMinLength = true,
		showTextareaMaxLength = true,
	} = attributes;

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

	let textareaValidationPatternOptions = [];

	if (typeof esFormsLocalization !== 'undefined' && isArray(esFormsLocalization?.validationPatternsOptions)) {
		textareaValidationPatternOptions = esFormsLocalization.validationPatternsOptions;
	}

	const [showValidation, setShowValidation] = useState(false);

	return (
		<>
			<PanelBody title={__('Multiline text', 'eightshift-forms')}>
				<FieldOptions
					{...props('field', attributes, {
						fieldDisabledOptions: textareaDisabledOptions,
					})}
				/>

				<TextControl
					label={<IconLabel icon={icons.fieldPlaceholder} label={__('Placeholder', 'eightshift-forms')} />}
					help={__('Shown when the field is empty', 'eightshift-forms')}
					value={textareaPlaceholder}
					onChange={(value) => setAttributes({ [getAttrKey('textareaPlaceholder', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('textareaPlaceholder', attributes, manifest), textareaDisabledOptions)}
				/>

				<FancyDivider label={__('Validation', 'eightshift-forms')} />

				<div className='es-h-spaced'>
					<Button
						icon={icons.fieldRequired}
						isPressed={textareaIsRequired}
						onClick={() => setAttributes({ [getAttrKey('textareaIsRequired', attributes, manifest)]: !textareaIsRequired })}
						disabled={isOptionDisabled(getAttrKey('textareaIsRequired', attributes, manifest), textareaDisabledOptions)}
					>
						{__('Required', 'eightshift-forms')}
					</Button>

					<Button
						icon={icons.regex}
						isPressed={textareaValidationPattern?.length > 0}
						onClick={() => setShowValidation(true)}
					>
						{__('Pattern validation', 'eightshift-forms')}

						{showValidation &&
							<Popover noArrow={false} onClose={() => setShowValidation(false)}>
								<div className='es-popover-content'>
									<SimpleVerticalSingleSelect
										label={__('Validation pattern', 'eightshift-forms')}
										options={textareaValidationPatternOptions.map(({ label, value }) => ({
											onClick: () => setAttributes({ [getAttrKey('textareaValidationPattern', attributes, manifest)]: value }),
											label: label,
											isActive: textareaValidationPattern === value,
										}))}
										disabled={isOptionDisabled(getAttrKey('textareaValidationPattern', attributes, manifest), textareaDisabledOptions)}
									/>
								</div>
							</Popover>
						}
					</Button>
				</div>

				<FancyDivider label={__('Advanced', 'eightshift-forms')} />

				<TextControl
					label={<IconLabel icon={icons.fieldName} label={__('Name', 'eightshift-forms')} />}
					help={__('Should be unique! Used to identify the field within form submission data.', 'eightshift-forms')}
					value={textareaName}
					onChange={(value) => setAttributes({ [getAttrKey('textareaName', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('textareaName', attributes, manifest), textareaDisabledOptions)}
				/>
				<MissingName value={textareaName} />

				<TextControl
					label={<IconLabel icon={icons.fieldValue} label={__('Initial value', 'eightshift-forms')} />}
					value={textareaValue}
					onChange={(value) => setAttributes({ [getAttrKey('textareaValue', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('textareaValue', attributes, manifest), textareaDisabledOptions)}
				/>

				<div className='es-h-spaced'>
					<Button
						icon={icons.fieldReadonly}
						isPressed={textareaIsReadOnly}
						onClick={() => setAttributes({ [getAttrKey('textareaIsReadOnly', attributes, manifest)]: !textareaIsReadOnly })}
						disabled={isOptionDisabled(getAttrKey('textareaIsReadOnly', attributes, manifest), textareaDisabledOptions)}
					>
						{__('Read-only', 'eightshift-forms')}
					</Button>

					<Button
						icon={icons.fieldDisabled}
						isPressed={textareaIsDisabled}
						onClick={() => setAttributes({ [getAttrKey('textareaIsDisabled', attributes, manifest)]: !textareaIsDisabled })}
						disabled={isOptionDisabled(getAttrKey('textareaIsDisabled', attributes, manifest), textareaDisabledOptions)}
					>
						{__('Disabled', 'eightshift-forms')}
					</Button>
				</div>

				{(showTextareaMinLength || showTextareaMaxLength) &&
					<FancyDivider label={__('Entry length', 'eightshift-forms')} />
				}

				{showTextareaMinLength &&
					<>
						<CustomSlider
							label={<IconLabel icon={icons.rangeMin} label={__('Smallest allowed length', 'eightshift-forms')} />}
							value={textareaMinLength ?? 0}
							onChange={(value) => setAttributes({ [getAttrKey('textareaMinLength', attributes, manifest)]: value })}
							min={options.textareaMinLength.min}
							step={options.textareaMinLength.step}
							hasValueDisplay
							rightAddition={
								<Button
									label={__('Reset', 'eightshift-forms')}
									icon={icons.rotateLeft}
									onClick={() => setAttributes({ [getAttrKey('textareaMinLength', attributes, manifest)]: undefined })}
									isSmall
									className='es-small-square-icon-button'
								/>
							}
							valueDisplayElement={(<span className='es-custom-slider-current-value'>{textareaMinLength ? parseInt(textareaMinLength) : '--'}</span>)}
							disabled={isOptionDisabled(getAttrKey('textareaMinLength', attributes, manifest), textareaDisabledOptions)}
						/>
					</>
				}

				{showTextareaMaxLength &&
					<CustomSlider
						label={<IconLabel icon={icons.rangeMax} label={__('Largest allowed length', 'eightshift-forms')} />}
						value={textareaMaxLength ?? 0}
						onChange={(value) => setAttributes({ [getAttrKey('textareaMaxLength', attributes, manifest)]: value })}
						min={options.textareaMaxLength.min}
						step={options.textareaMaxLength.step}
						hasValueDisplay
						rightAddition={
							<Button
								label={__('Reset', 'eightshift-forms')}
								icon={icons.rotateLeft}
								onClick={() => setAttributes({ [getAttrKey('textareaMaxLength', attributes, manifest)]: undefined })}
								isSmall
								className='es-small-square-icon-button'
							/>
						}
						valueDisplayElement={(<span className='es-custom-slider-current-value'>{textareaMaxLength ? parseInt(textareaMaxLength) : '--'}</span>)}
						disabled={isOptionDisabled(getAttrKey('textareaMaxLength', attributes, manifest), textareaDisabledOptions)}
					/>
				}

				<FancyDivider label={__('Tracking', 'eightshift-forms')} />

				<TextControl
					label={<IconLabel icon={icons.code} label={__('GTM tracking code', 'eightshift-forms')} />}
					value={textareaTracking}
					onChange={(value) => setAttributes({ [getAttrKey('textareaTracking', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('textareaTracking', attributes, manifest), textareaDisabledOptions)}
				/>
			</PanelBody>

			<FieldOptionsAdvanced
				{...props('field', attributes)}
			/>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsParentName: textareaName,
				})}
			/>
		</>
	);
};
