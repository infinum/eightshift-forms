/* global esFormsBlocksLocalization */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { isArray } from 'lodash';
import { useState } from '@wordpress/element';
import { TextControl, SelectControl } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	IconToggle,
	props,
	ComponentUseToggle
} from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../../components/field/components/field-options';
import { FieldOptionsAdvanced } from '../../field/components/field-options-advanced';
import manifest from '../manifest.json';

export const TextareaOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const textareaName = checkAttr('textareaName', attributes, manifest);
	const textareaValue = checkAttr('textareaValue', attributes, manifest);
	const textareaPlaceholder = checkAttr('textareaPlaceholder', attributes, manifest);
	const textareaIsDisabled = checkAttr('textareaIsDisabled', attributes, manifest);
	const textareaIsReadOnly = checkAttr('textareaIsReadOnly', attributes, manifest);
	const textareaIsRequired = checkAttr('textareaIsRequired', attributes, manifest);
	const textareaTracking = checkAttr('textareaTracking', attributes, manifest);
	const textareaValidationPattern = checkAttr('textareaValidationPattern', attributes, manifest);

	const [showAdvanced, setShowAdvanced] = useState(false);
	const [showValidation, setShowValidation] = useState(false);

	let textareaValidationPatternOptions = [];

	if (typeof esFormsBlocksLocalization !== 'undefined' && isArray(esFormsBlocksLocalization?.validationPatternsOptions)) {
		textareaValidationPatternOptions = esFormsBlocksLocalization.validationPatternsOptions;
	}

	return (
		<>
			<FieldOptions
				{...props('field', attributes)}
			/>

			<TextControl
				label={<IconLabel icon={icons.fieldPlaceholder} label={__('Placeholder', 'eightshift-forms')} />}
				help={__('Set text used as a placeholder before user starts typing.', 'eightshift-forms')}
				value={textareaPlaceholder}
				onChange={(value) => setAttributes({ [getAttrKey('textareaPlaceholder', attributes, manifest)]: value })}
			/>

			<ComponentUseToggle
				label={__('Show advanced options', 'eightshift-forms')}
				checked={showAdvanced}
				onChange={() => setShowAdvanced(!showAdvanced)}
				showUseToggle={true}
				showLabel={true}
			/>

			{showAdvanced &&
				<>
					<TextControl
						label={<IconLabel icon={icons.fieldName} label={__('Name', 'eightshift-forms')} />}
						help={__('Set unique field name. If not set field will have an generic name.', 'eightshift-forms')}
						value={textareaName}
						onChange={(value) => setAttributes({ [getAttrKey('textareaName', attributes, manifest)]: value })}
					/>

					<TextControl
						label={<IconLabel icon={icons.fieldValue} label={__('Value', 'eightshift-forms')} />}
						help={__('Provide value that is going to be preset for the field.', 'eightshift-forms')}
						value={textareaValue}
						onChange={(value) => setAttributes({ [getAttrKey('textareaValue', attributes, manifest)]: value })}
					/>

					<TextControl
						label={<IconLabel icon={icons.code} label={__('Tracking Code', 'eightshift-forms')} />}
						help={__('Provide GTM tracking code.', 'eightshift-forms')}
						value={textareaTracking}
						onChange={(value) => setAttributes({ [getAttrKey('textareaTracking', attributes, manifest)]: value })}
					/>

					<IconToggle
						icon={icons.fieldDisabled}
						label={__('Is Disabled', 'eightshift-forms')}
						checked={textareaIsDisabled}
						onChange={(value) => setAttributes({ [getAttrKey('textareaIsDisabled', attributes, manifest)]: value })}
					/>

					<IconToggle
						icon={icons.fieldReadonly}
						label={__('Is Read Only', 'eightshift-forms')}
						checked={textareaIsReadOnly}
						onChange={(value) => setAttributes({ [getAttrKey('textareaIsReadOnly', attributes, manifest)]: value })}
					/>
				</>
			}

			<ComponentUseToggle
				label={__('Show validation options', 'eightshift-forms')}
				checked={showValidation}
				onChange={() => setShowValidation(!showValidation)}
				showUseToggle={true}
				showLabel={true}
			/>

			{showValidation &&
				<>
					<IconToggle
						icon={icons.fieldRequired}
						label={__('Is Required', 'eightshift-forms')}
						checked={textareaIsRequired}
						onChange={(value) => setAttributes({ [getAttrKey('textareaIsRequired', attributes, manifest)]: value })}
					/>

					<SelectControl
						label={<IconLabel icon={icons.regex} label={__('Validation Pattern', 'eightshift-forms')} />}
						help={__('Provide validation pattern in a form of regular expression for specific validation.', 'eightshift-forms')}
						value={textareaValidationPattern}
						options={textareaValidationPatternOptions}
						onChange={(value) => setAttributes({ [getAttrKey('textareaValidationPattern', attributes, manifest)]: value })}
					/>
				</>
			}

			<FieldOptionsAdvanced
				{...props('field', attributes)}
			/>
		</>
	);
};
