/* global esFormsBlocksLocalization */

import React from 'react';
import { isArray } from 'lodash';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { TextControl, SelectControl, RangeControl } from '@wordpress/components';
import {
	icons,
	getOption,
	checkAttr,
	getAttrKey,
	IconLabel,
	IconToggle,
	props,
	ComponentUseToggle
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
		showInputIsEmail = true,
		showInputIsUrl = true,
		showInputValidationPattern = true,
		showInputMin = true,
		showInputMax = true,
		showInputStep = true,
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
	const inputMin = checkAttr('inputMin', attributes, manifest);
	const inputMax = checkAttr('inputMax', attributes, manifest);
	const inputStep = checkAttr('inputStep', attributes, manifest);

	const [showAdvanced, setShowAdvanced] = useState(false);
	const [showValidation, setShowValidation] = useState(false);

	let inputValidationPatternOptions = [];

	if (typeof esFormsBlocksLocalization !== 'undefined' && isArray(esFormsBlocksLocalization?.validationPatternsOptions)) {
		inputValidationPatternOptions = esFormsBlocksLocalization.validationPatternsOptions;
	}

	return (
		<>
			<FieldOptions
				{...props('field', attributes)}
			/>

			{showInputPlaceholder &&
				<TextControl
					label={<IconLabel icon={icons.fieldPlaceholder} label={__('Placeholder', 'eightshift-forms')} />}
					help={__('Set text used as a placeholder before user starts typing.', 'eightshift-forms')}
					value={inputPlaceholder}
					onChange={(value) => setAttributes({ [getAttrKey('inputPlaceholder', attributes, manifest)]: value })}
				/>
			}

			{showInputType &&
				<SelectControl
					label={<IconLabel icon={icons.fieldType} label={__('Type', 'eightshift-forms')} />}
					help={__('Set what type of input filed it is used.', 'eightshift-forms')}
					value={inputType}
					options={getOption('inputType', attributes, manifest)}
					onChange={(value) => setAttributes({ [getAttrKey('inputType', attributes, manifest)]: value })}
				/>
			}

			{showInputAdvancedOptions &&
				<>
					<ComponentUseToggle
						label={__('Show advanced options', 'eightshift-forms')}
						checked={showAdvanced}
						onChange={() => setShowAdvanced(!showAdvanced)}
						showUseToggle={true}
						showLabel={true}
					/>

					{showAdvanced &&
						<>
							{showInputName &&
								<TextControl
								label={<IconLabel icon={icons.fieldName} label={__('Name', 'eightshift-forms')} />}
									help={__('Set unique field name. If not set field will have an generic name.', 'eightshift-forms')}
									value={inputName}
									onChange={(value) => setAttributes({ [getAttrKey('inputName', attributes, manifest)]: value })}
								/>
							}

							{showInputValue &&
								<TextControl
								label={<IconLabel icon={icons.fieldValue} label={__('Value', 'eightshift-forms')} />}
									help={__('Provide value that is going to be preset for the field.', 'eightshift-forms')}
									value={inputValue}
									onChange={(value) => setAttributes({ [getAttrKey('inputValue', attributes, manifest)]: value })}
								/>
							}

							{showInputTracking &&
								<TextControl
									label={<IconLabel icon={icons.code} label={__('Tracking Code', 'eightshift-forms')} />}
									help={__('Provide GTM tracking code.', 'eightshift-forms')}
									value={inputTracking}
									onChange={(value) => setAttributes({ [getAttrKey('inputTracking', attributes, manifest)]: value })}
								/>
							}

							{showInputIsDisabled &&
								<IconToggle
									icon={icons.fieldDisabled}
									label={__('Is Disabled', 'eightshift-forms')}
									checked={inputIsDisabled}
									onChange={(value) => setAttributes({ [getAttrKey('inputIsDisabled', attributes, manifest)]: value })}
								/>
							}

							{showInputIsReadOnly &&
								<IconToggle
									icon={icons.fieldReadonly}
									label={__('Is Read Only', 'eightshift-forms')}
									checked={inputIsReadOnly}
									onChange={(value) => setAttributes({ [getAttrKey('inputIsReadOnly', attributes, manifest)]: value })}
								/>
							}
						</>
					}
				</>
			}

			{showInputValidationOptions &&
				<>
					<ComponentUseToggle
						label={__('Show validation options', 'eightshift-forms')}
						checked={showValidation}
						onChange={() => setShowValidation(!showValidation)}
						showUseToggle={true}
						showLabel={true}
					/>

					{showValidation &&
						<>
							{showInputIsRequired &&
								<IconToggle
									icon={icons.fieldRequired}
									label={__('Is Required', 'eightshift-forms')}
									checked={inputIsRequired}
									onChange={(value) => setAttributes({ [getAttrKey('inputIsRequired', attributes, manifest)]: value })}
								/>
							}

							{(showInputIsEmail && !inputIsUrl && inputValidationPattern == '' && inputType !== 'number') &&
								<IconToggle
									icon={icons.email}
									label={__('Is Email', 'eightshift-forms')}
									checked={inputIsEmail}
									onChange={(value) => setAttributes({ [getAttrKey('inputIsEmail', attributes, manifest)]: value })}
								/>
							}

							{(showInputIsUrl && !inputIsEmail && inputValidationPattern === '' && inputType !== 'number') &&
								<IconToggle
									icon={icons.link}
									label={__('Is Url', 'eightshift-forms')}
									checked={inputIsUrl}
									onChange={(value) => setAttributes({ [getAttrKey('inputIsUrl', attributes, manifest)]: value })}
								/>
							}

							{(showInputValidationPattern && !inputIsUrl && !inputIsEmail) &&
								<SelectControl
									label={<IconLabel icon={icons.regex} label={__('Validation Pattern', 'eightshift-forms')} />}
									help={__('Provide validation pattern in a form of regular expression for specific validation.', 'eightshift-forms')}
									value={inputValidationPattern}
									options={inputValidationPatternOptions}
									onChange={(value) => setAttributes({ [getAttrKey('inputValidationPattern', attributes, manifest)]: value })}
								/>
							}

							{(inputType === 'number' && showInputMin) &&
								<RangeControl
								label={<IconLabel icon={icons.rangeMin} label={__('Validation Min Number', 'eightshift-forms')} />}
									help={__('Set minimum number a user can enter', 'eightshift-forms')}
									allowReset={true}
									value={inputMin}
									onChange={(value) => setAttributes({ [getAttrKey('inputMin', attributes, manifest)]: value })}
									min={options.inputMin.min}
									step={options.inputMin.step}
								/>
							}

							{(inputType === 'number' && showInputMax) &&
								<RangeControl
								label={<IconLabel icon={icons.rangeMax} label={__('Validation Max Number', 'eightshift-forms')} />}
									help={__('Set maximum number a user can enter', 'eightshift-forms')}
									allowReset={true}
									value={inputMax}
									onChange={(value) => setAttributes({ [getAttrKey('inputMax', attributes, manifest)]: value })}
									min={options.inputMax.min}
									step={options.inputMax.step}
								/>
							}

							{(inputType === 'number' && showInputStep) &&
								<RangeControl
								label={<IconLabel icon={icons.step} label={__('Validation Step Number', 'eightshift-forms')} />}
									help={__('Set step number a user can change', 'eightshift-forms')}
									allowReset={true}
									value={inputStep}
									onChange={(value) => setAttributes({ [getAttrKey('inputStep', attributes, manifest)]: value })}
									min={options.inputStep.min}
									step={options.inputStep.step}
								/>
							}
						</>
					}
				</>
			}

			<FieldOptionsAdvanced
				{...props('field', attributes)}
			/>
		</>
	);
};
