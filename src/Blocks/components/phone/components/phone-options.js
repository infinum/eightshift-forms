/* global esFormsLocalization */

import React from 'react';
import { useState } from '@wordpress/element';
import { select } from '@wordpress/data';
import { isArray } from 'lodash';
import { __ } from '@wordpress/i18n';
import { TextControl, PanelBody } from '@wordpress/components';
import {icons,checkAttr,getAttrKey,IconLabel,props,IconToggle,Section,Select, STORE_NAME} from '@eightshift/frontend-libs/scripts';
import { FieldOptions, FieldOptionsMore, FieldOptionsLayout, FieldOptionsVisibility } from '../../field/components/field-options';
import { isOptionDisabled, NameFieldLabel, NameChangeWarning } from '../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';

export const PhoneOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('phone');

	const {
		setAttributes,
		title = __('Phone', 'eightshift-forms'),
	} = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const phoneName = checkAttr('phoneName', attributes, manifest);
	const phoneValue = checkAttr('phoneValue', attributes, manifest);
	const phonePlaceholder = checkAttr('phonePlaceholder', attributes, manifest);
	const phoneIsNumber = checkAttr('phoneIsNumber', attributes, manifest);
	const phoneIsDisabled = checkAttr('phoneIsDisabled', attributes, manifest);
	const phoneIsReadOnly = checkAttr('phoneIsReadOnly', attributes, manifest);
	const phoneIsRequired = checkAttr('phoneIsRequired', attributes, manifest);
	const phoneTracking = checkAttr('phoneTracking', attributes, manifest);
	const phoneValidationPattern = checkAttr('phoneValidationPattern', attributes, manifest);
	const phoneDisabledOptions = checkAttr('phoneDisabledOptions', attributes, manifest);
	const phoneUseSearch = checkAttr('phoneUseSearch', attributes, manifest);
	const phoneUseLabelAsPlaceholder = checkAttr('phoneUseLabelAsPlaceholder', attributes, manifest);

	let phoneValidationPatternOptions = [];

	if (typeof esFormsLocalization !== 'undefined' && isArray(esFormsLocalization?.validationPatternsOptions)) {
		phoneValidationPatternOptions = esFormsLocalization.validationPatternsOptions;
	}

	return (
		<>
			<PanelBody title={title}>
				<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
					<TextControl
						label={<NameFieldLabel value={phoneName} />}
						help={__('Identifies the field within form submission data. Must be unique.', 'eightshift-forms')}
						value={phoneName}
						onChange={(value) => {
							setIsNameChanged(true);
							setAttributes({ [getAttrKey('phoneName', attributes, manifest)]: value });
						}}
						disabled={isOptionDisabled(getAttrKey('phoneName', attributes, manifest), phoneDisabledOptions)}
					/>
					<NameChangeWarning isChanged={isNameChanged} />
				</Section>

				<FieldOptions
					{...props('field', attributes, {
						fieldDisabledOptions: phoneDisabledOptions,
					})}
				/>

				<Section icon={icons.fieldPlaceholder} label={__('Placeholder', 'eightshift-forms')}>
					{!phoneUseLabelAsPlaceholder &&
						<TextControl
							help={__('Shown when the field is empty', 'eightshift-forms')}
							value={phonePlaceholder}
							onChange={(value) => setAttributes({ [getAttrKey('phonePlaceholder', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('phonePlaceholder', attributes, manifest), phoneDisabledOptions)}
							className='es-no-field-spacing'
						/>
					}
					<IconToggle
						icon={icons.fieldPlaceholder}
						label={__('Use label as placeholder', 'eightshift-forms')}
						checked={phoneUseLabelAsPlaceholder}
						onChange={(value) => {
							setAttributes({ [getAttrKey('phonePlaceholder', attributes, manifest)]: undefined });
							setAttributes({ [getAttrKey('phoneUseLabelAsPlaceholder', attributes, manifest)]: value });
						}}
					/>
				</Section>

				<FieldOptionsLayout
					{...props('field', attributes, {
						fieldDisabledOptions: phoneDisabledOptions,
					})}
				/>

				<Section icon={icons.tools} label={__('Advanced', 'eightshift-forms')}>
					<TextControl
						label={<IconLabel icon={icons.fieldValue} label={__('Initial value', 'eightshift-forms')} />}
						value={phoneValue}
						onChange={(value) => setAttributes({ [getAttrKey('phoneValue', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('phoneValue', attributes, manifest), phoneDisabledOptions)}
					/>

					<FieldOptionsVisibility
						{...props('field', attributes, {
							fieldDisabledOptions: phoneDisabledOptions,
						})}
					/>

					<IconToggle
						icon={icons.readOnly}
						label={__('Read-only', 'eightshift-forms')}
						checked={phoneIsReadOnly}
						onChange={(value) => setAttributes({ [getAttrKey('phoneIsReadOnly', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('phoneIsReadOnly', attributes, manifest), phoneDisabledOptions)}
					/>

					<IconToggle
						icon={icons.cursorDisabled}
						label={__('Disabled', 'eightshift-forms')}
						checked={phoneIsDisabled}
						onChange={(value) => setAttributes({ [getAttrKey('phoneIsDisabled', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('phoneIsDisabled', attributes, manifest), phoneDisabledOptions)}
					/>

					<IconToggle
						icon={icons.order}
						label={__('Number', 'eightshift-forms')}
						checked={phoneIsNumber}
						onChange={(value) => setAttributes({ [getAttrKey('phoneIsNumber', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('phoneIsNumber', attributes, manifest), phoneDisabledOptions)}
					/>

					<IconToggle
						icon={icons.search}
						label={__('Search', 'eightshift-forms')}
						checked={phoneUseSearch}
						onChange={(value) => setAttributes({ [getAttrKey('phoneUseSearch', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('phoneUseSearch', attributes, manifest), phoneDisabledOptions)}
						noBottomSpacing
					/>
				</Section>

				<Section icon={icons.checks} label={__('Validation', 'eightshift-forms')}>
					<IconToggle
						icon={icons.required}
						label={__('Required', 'eightshift-forms')}
						checked={phoneIsRequired}
						onChange={(value) => setAttributes({ [getAttrKey('phoneIsRequired', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('phoneIsRequired', attributes, manifest), phoneDisabledOptions)}
					/>

					<Select
						icon={icons.regex}
						label={__('Match pattern', 'eightshift-forms')}
						options={phoneValidationPatternOptions}
						value={phoneValidationPattern}
						onChange={(value) => setAttributes({ [getAttrKey('phoneValidationPattern', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('phoneValidationPattern', attributes, manifest), phoneDisabledOptions)}
						placeholder='–'
						additionalSelectClasses='es-w-32'
						noBottomSpacing
						inlineLabel
						clearable
					/>
				</Section>

				<Section icon={icons.alignHorizontalVertical} label={__('Tracking', 'eightshift-forms')} collapsable>
					<TextControl
						label={<IconLabel icon={icons.googleTagManager} label={__('GTM tracking code', 'eightshift-forms')} />}
						value={phoneTracking}
						onChange={(value) => setAttributes({ [getAttrKey('phoneTracking', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('phoneTracking', attributes, manifest), phoneDisabledOptions)}
						className='es-no-field-spacing'
					/>
				</Section>

				<FieldOptionsMore
					{...props('field', attributes, {
						fieldDisabledOptions: phoneDisabledOptions,
					})}
				/>
			</PanelBody>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: phoneName,
					conditionalTagsIsHidden: checkAttr('phoneFieldHidden', attributes, manifest),
				})}
			/>
		</>
	);
};
