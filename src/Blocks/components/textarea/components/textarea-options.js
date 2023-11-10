/* global esFormsLocalization */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { isArray } from 'lodash';
import { select } from '@wordpress/data';
import { TextControl, PanelBody, Button } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	props,
	Section,
	Select,
	IconToggle,
	NumberPicker,
	Control,
	STORE_NAME,
} from '@eightshift/frontend-libs/scripts';
import { FieldOptions, FieldOptionsMore, FieldOptionsLayout, FieldOptionsVisibility } from '../../field/components/field-options';
import { isOptionDisabled, NameField } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';

export const TextareaOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('textarea');

	const {
		options,
	} = manifest;

	const {
		setAttributes,

		showTextareaMinLength = true,
		showTextareaMaxLength = true,
	} = attributes;

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

	if (typeof esFormsLocalization !== 'undefined' && isArray(esFormsLocalization?.validationPatternsOptions)) {
		textareaValidationPatternOptions = esFormsLocalization.validationPatternsOptions;
	}

	return (
		<>
			<PanelBody title={__('Multiline text', 'eightshift-forms')}>
				<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
					<NameField
						value={textareaName}
						attribute={getAttrKey('textareaName', attributes, manifest)}
						disabledOptions={textareaDisabledOptions}
						setAttributes={setAttributes}
						type={'textarea'}
						isChanged={isNameChanged}
						setIsChanged={setIsNameChanged}
					/>
				</Section>

				<FieldOptions
					{...props('field', attributes, {
						fieldDisabledOptions: textareaDisabledOptions,
					})}
				/>

				<Section icon={icons.fieldPlaceholder} label={__('Placeholder', 'eightshift-forms')}>
					{!textareaUseLabelAsPlaceholder &&
						<TextControl
							help={__('Shown when the field is empty', 'eightshift-forms')}
							value={textareaPlaceholder}
							onChange={(value) => setAttributes({ [getAttrKey('textareaPlaceholder', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('textareaPlaceholder', attributes, manifest), textareaDisabledOptions)}
							className='es-no-field-spacing'
						/>
					}
					<IconToggle
						icon={icons.fieldPlaceholder}
						label={__('Use label as placeholder', 'eightshift-forms')}
						checked={textareaUseLabelAsPlaceholder}
						onChange={(value) => {
							setAttributes({ [getAttrKey('textareaPlaceholder', attributes, manifest)]: undefined });
							setAttributes({ [getAttrKey('textareaUseLabelAsPlaceholder', attributes, manifest)]: value });
						}}
					/>
				</Section>

				<FieldOptionsLayout
					{...props('field', attributes, {
						fieldDisabledOptions: textareaDisabledOptions,
					})}
				/>

				<Section icon={icons.checks} label={__('Validation', 'eightshift-forms')}>
					<IconToggle
						icon={icons.required}
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
						disabled={isOptionDisabled(getAttrKey('textareaValidationPattern', attributes, manifest), textareaDisabledOptions)}
						placeholder='–'
						additionalSelectClasses='es-w-32'
						inlineLabel
						clearable
					/>

					{(showTextareaMinLength || showTextareaMaxLength) &&
						<Control
							icon={icons.textLength}
							label={__('Entry length', 'eightshift-forms')}
							additionalLabelClasses='es-mb-0!'
							noBottomSpacing
						>
							<div className='es-h-spaced es-gap-5!'>
								{showTextareaMinLength &&
									<div className='es-display-flex es-items-end es-gap-2'>
										<NumberPicker
											label={__('Min', 'eightshift-forms')}
											value={textareaMinLength}
											onChange={(value) => setAttributes({ [getAttrKey('textareaMinLength', attributes, manifest)]: value })}
											min={options.textareaMinLength.min}
											step={options.textareaMinLength.step}
											disabled={isOptionDisabled(getAttrKey('textareaMinLength', attributes, manifest), textareaDisabledOptions)}
											placeholder='–'
											fixedWidth={4}
											noBottomSpacing
										/>

										{textareaMinLength > 0 && !isOptionDisabled(getAttrKey('textareaMinLength', attributes, manifest), textareaDisabledOptions) &&
											<Button
												label={__('Disable', 'eightshift-forms')}
												icon={icons.clear}
												onClick={() => setAttributes({ [getAttrKey('textareaMinLength', attributes, manifest)]: undefined })}
												className='es-button-square-32 es-button-icon-24'
												showTooltip
												isSmall
											/>
										}
									</div>
								}

								{showTextareaMaxLength &&
									<div className='es-display-flex es-items-end es-gap-2'>
										<NumberPicker
											label={__('Max', 'eightshift-forms')}
											value={textareaMaxLength}
											onChange={(value) => setAttributes({ [getAttrKey('textareaMaxLength', attributes, manifest)]: value })}
											min={options.textareaMaxLength.min}
											step={options.textareaMaxLength.step}
											disabled={isOptionDisabled(getAttrKey('textareaMaxLength', attributes, manifest), textareaDisabledOptions)}
											placeholder='–'
											fixedWidth={4}
											noBottomSpacing
										/>

										{textareaMaxLength > 0 && !isOptionDisabled(getAttrKey('textareaMaxLength', attributes, manifest), textareaDisabledOptions) &&
											<Button
												label={__('Disable', 'eightshift-forms')}
												icon={icons.clear}
												onClick={() => setAttributes({ [getAttrKey('textareaMaxLength', attributes, manifest)]: undefined })}
												className='es-button-square-32 es-button-icon-24'
												showTooltip
											/>
										}
									</div>
								}
							</div>
						</Control>
					}
				</Section>

				<Section icon={icons.tools} label={__('Advanced', 'eightshift-forms')}>
					<TextControl
						label={<IconLabel icon={icons.fieldValue} label={__('Initial value', 'eightshift-forms')} />}
						value={textareaValue}
						onChange={(value) => setAttributes({ [getAttrKey('textareaValue', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('textareaValue', attributes, manifest), textareaDisabledOptions)}
					/>

					<FieldOptionsVisibility
						{...props('field', attributes, {
							fieldDisabledOptions: textareaDisabledOptions,
						})}
					/>

					<IconToggle
						icon={icons.readOnly}
						label={__('Read-only', 'eightshift-forms')}
						checked={textareaIsReadOnly}
						onChange={(value) => setAttributes({ [getAttrKey('textareaIsReadOnly', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('textareaIsReadOnly', attributes, manifest), textareaDisabledOptions)}
					/>

					<IconToggle
						icon={icons.cursorDisabled}
						label={__('Disabled', 'eightshift-forms')}
						checked={textareaIsDisabled}
						onChange={(value) => setAttributes({ [getAttrKey('textareaIsDisabled', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('textareaIsDisabled', attributes, manifest), textareaDisabledOptions)}
						noBottomSpacing
					/>
				</Section>

				<Section icon={icons.alignHorizontalVertical} label={__('Tracking', 'eightshift-forms')} collapsable>
					<TextControl
						label={<IconLabel icon={icons.googleTagManager} label={__('GTM tracking code', 'eightshift-forms')} />}
						value={textareaTracking}
						onChange={(value) => setAttributes({ [getAttrKey('textareaTracking', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('textareaTracking', attributes, manifest), textareaDisabledOptions)}
						className='es-no-field-spacing'
					/>
				</Section>

				<FieldOptionsMore
					{...props('field', attributes, {
						fieldDisabledOptions: textareaDisabledOptions,
					})}
				/>
			</PanelBody>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: textareaName,
					conditionalTagsIsHidden: checkAttr('textareaFieldHidden', attributes, manifest),
				})}
			/>
		</>
	);
};
