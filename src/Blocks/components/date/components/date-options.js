/* global esFormsLocalization */

import React from 'react';
import { isArray } from 'lodash';
import { select } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { TextControl, PanelBody } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	props,
	Section,
	Select,
	IconToggle,
	getOption,
	STORE_NAME,
} from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../field/components/field-options';
import { FieldOptionsAdvanced } from '../../field/components/field-options-advanced';
import { isOptionDisabled, NameFieldLabel, NameChangeWarning } from '../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';

export const DateOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('date');

	const {
		setAttributes,
		title = __('Date', 'eightshift-forms'),
	} = attributes;

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

	let dateValidationPatternOptions = [];

	if (typeof esFormsLocalization !== 'undefined' && isArray(esFormsLocalization?.validationPatternsOptions)) {
		dateValidationPatternOptions = esFormsLocalization.validationPatternsOptions;
	}

	return (
		<>
			<PanelBody title={title}>
				<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
					<TextControl
						label={<NameFieldLabel value={dateName} />}
						help={__('Identifies the field within form submission data. Must be unique.', 'eightshift-forms')}
						value={dateName}
						onChange={(value) => {
							setIsNameChanged(true);
							setAttributes({ [getAttrKey('dateName', attributes, manifest)]: value });
						}}
						disabled={isOptionDisabled(getAttrKey('dateName', attributes, manifest), dateDisabledOptions)}
					/>
					<NameChangeWarning isChanged={isNameChanged} />

					<Select
						icon={icons.optionListAlt}
						label={__('Type', 'eightshift-forms')}
						value={dateType}
						options={getOption('dateType', attributes, manifest)}
						disabled={isOptionDisabled(getAttrKey('dateType', attributes, manifest), dateDisabledOptions)}
						onChange={(value) => setAttributes({ [getAttrKey('dateType', attributes, manifest)]: value })}
						additionalSelectClasses='es-w-32'
						simpleValue
						inlineLabel
						noSearch
					/>

					{!dateUseLabelAsPlaceholder &&
						<TextControl
							label={<IconLabel icon={icons.fieldPlaceholder} label={__('Placeholder', 'eightshift-forms')} />}
							help={__('Shown when the field is empty', 'eightshift-forms')}
							value={datePlaceholder}
							onChange={(value) => setAttributes({ [getAttrKey('datePlaceholder', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('datePlaceholder', attributes, manifest), dateDisabledOptions)}
							className='es-no-field-spacing'
						/>
					}
					<IconToggle
						icon={icons.fieldPlaceholder}
						label={__('Use label as placeholder', 'eightshift-forms')}
						checked={dateUseLabelAsPlaceholder}
						onChange={(value) => {
							setAttributes({ [getAttrKey('datePlaceholder', attributes, manifest)]: undefined });
							setAttributes({ [getAttrKey('dateUseLabelAsPlaceholder', attributes, manifest)]: value });
						}}
					/>
				</Section>

				<FieldOptions
					{...props('field', attributes, {
						fieldDisabledOptions: dateDisabledOptions,
					})}
					additionalControls={<FieldOptionsAdvanced {...props('field', attributes)} />}
				/>

				<Section icon={icons.checks} label={__('Validation', 'eightshift-forms')}>
					<IconToggle
						icon={icons.required}
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
						additionalSelectClasses='es-w-32'
						noBottomSpacing
						inlineLabel
						clearable
					/>
				</Section>

				<Section icon={icons.tools} label={__('Advanced', 'eightshift-forms')}>
					<TextControl
						label={<IconLabel icon={icons.fieldValue} label={__('Initial value', 'eightshift-forms')} />}
						value={dateValue}
						onChange={(value) => setAttributes({ [getAttrKey('dateValue', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('dateValue', attributes, manifest), dateDisabledOptions)}
					/>

					<IconToggle
						icon={icons.readOnly}
						label={__('Read-only', 'eightshift-forms')}
						checked={dateIsReadOnly}
						onChange={(value) => setAttributes({ [getAttrKey('dateIsReadOnly', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('dateIsReadOnly', attributes, manifest), dateDisabledOptions)}
					/>

					<IconToggle
						icon={icons.cursorDisabled}
						label={__('Disabled', 'eightshift-forms')}
						checked={dateIsDisabled}
						onChange={(value) => setAttributes({ [getAttrKey('dateIsDisabled', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('dateIsDisabled', attributes, manifest), dateDisabledOptions)}
						noBottomSpacing
					/>
				</Section>

				<Section icon={icons.alignHorizontalVertical} label={__('Tracking', 'eightshift-forms')} noBottomSpacing collapsable>
					<TextControl
						label={<IconLabel icon={icons.googleTagManager} label={__('GTM tracking code', 'eightshift-forms')} />}
						value={dateTracking}
						onChange={(value) => setAttributes({ [getAttrKey('dateTracking', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('dateTracking', attributes, manifest), dateDisabledOptions)}
						className='es-no-field-spacing'
					/>
				</Section>
			</PanelBody>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: dateName,
					conditionalTagsIsHidden: checkAttr('dateFieldHidden', attributes, manifest),
				})}
			/>
		</>
	);
};
