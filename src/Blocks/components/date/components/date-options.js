/* global esFormsLocalization */

import React from 'react';
import { isArray } from 'lodash';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { TextControl, PanelBody, Button, Popover } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	props,
	FancyDivider,
	SimpleVerticalSingleSelect,
} from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../field/components/field-options';
import { FieldOptionsAdvanced } from '../../field/components/field-options-advanced';
import manifest from '../manifest.json';
import { isOptionDisabled, MissingName } from '../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';

export const DateOptions = (attributes) => {
	const {
		setAttributes,
		title = __('Date', 'eightshift-forms'),
	} = attributes;

	const dateName = checkAttr('dateName', attributes, manifest);
	const dateValue = checkAttr('dateValue', attributes, manifest);
	const datePlaceholder = checkAttr('datePlaceholder', attributes, manifest);
	const dateIsDisabled = checkAttr('dateIsDisabled', attributes, manifest);
	const dateIsReadOnly = checkAttr('dateIsReadOnly', attributes, manifest);
	const dateIsRequired = checkAttr('dateIsRequired', attributes, manifest);
	const dateTracking = checkAttr('dateTracking', attributes, manifest);
	const dateValidationPattern = checkAttr('dateValidationPattern', attributes, manifest);
	const dateDisabledOptions = checkAttr('dateDisabledOptions', attributes, manifest);

	const [showValidation, setShowValidation] = useState(false);

	let dateValidationPatternOptions = [];

	if (typeof esFormsLocalization !== 'undefined' && isArray(esFormsLocalization?.validationPatternsOptions)) {
		dateValidationPatternOptions = esFormsLocalization.validationPatternsOptions;
	}

	return (
		<>
			<PanelBody title={title}>
				<FieldOptions
					{...props('field', attributes, {
						fieldDisabledOptions: dateDisabledOptions,
					})}
				/>

				<TextControl
					label={<IconLabel icon={icons.fieldPlaceholder} label={__('Placeholder', 'eightshift-forms')} />}
					help={__('Shown when the field is empty', 'eightshift-forms')}
					value={datePlaceholder}
					onChange={(value) => setAttributes({ [getAttrKey('datePlaceholder', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('datePlaceholder', attributes, manifest), dateDisabledOptions)}
				/>

				<FancyDivider label={__('Advanced', 'eightshift-forms')} />

				<TextControl
					label={<IconLabel icon={icons.fieldName} label={__('Name', 'eightshift-forms')} />}
					help={__('Should be unique! Used to identify the field within form submission data.', 'eightshift-forms')}
					value={dateName}
					onChange={(value) => setAttributes({ [getAttrKey('dateName', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('dateName', attributes, manifest), dateDisabledOptions)}
				/>
				<MissingName value={dateName} />

				<TextControl
					label={<IconLabel icon={icons.fieldValue} label={__('Initial value', 'eightshift-forms')} />}
					value={dateValue}
					onChange={(value) => setAttributes({ [getAttrKey('dateValue', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('dateValue', attributes, manifest), dateDisabledOptions)}
				/>

				<div className='es-h-spaced'>
					<Button
						icon={icons.fieldReadonly}
						isPressed={dateIsReadOnly}
						onClick={() => setAttributes({ [getAttrKey('dateIsReadOnly', attributes, manifest)]: !dateIsReadOnly })}
						disabled={isOptionDisabled(getAttrKey('dateIsReadOnly', attributes, manifest), dateDisabledOptions)}
					>
						{__('Read-only', 'eightshift-forms')}
					</Button>


					<Button
						icon={icons.fieldDisabled}
						isPressed={dateIsDisabled}
						onClick={() => setAttributes({ [getAttrKey('dateIsDisabled', attributes, manifest)]: !dateIsDisabled })}
						disabled={isOptionDisabled(getAttrKey('dateIsDisabled', attributes, manifest), dateDisabledOptions)}
					>
						{__('Disabled', 'eightshift-forms')}
					</Button>
				</div>

				<FancyDivider label={__('Validation', 'eightshift-forms')} />

				<div className='es-h-spaced-wrap'>
					<Button
						icon={icons.fieldRequired}
						isPressed={dateIsRequired}
						onClick={() => setAttributes({ [getAttrKey('dateIsRequired', attributes, manifest)]: !dateIsRequired })}
						disabled={isOptionDisabled(getAttrKey('dateIsRequired', attributes, manifest), dateDisabledOptions)}
					>
						{__('Required', 'eightshift-forms')}
					</Button>

					<Button
						icon={icons.regex}
						isPressed={dateValidationPattern?.length > 0}
						onClick={() => setShowValidation(true)}
					>
						{__('Pattern validation', 'eightshift-forms')}

						{showValidation &&
							<Popover noArrow={false} onClose={() => setShowValidation(false)}>
								<div className='es-popover-content'>
									<SimpleVerticalSingleSelect
										label={__('Validation pattern', 'eightshift-forms')}
										options={dateValidationPatternOptions.map(({ label, value }) => ({
											onClick: () => setAttributes({ [getAttrKey('dateValidationPattern', attributes, manifest)]: value }),
											label: label,
											isActive: dateValidationPattern === value,
										}))}
										disabled={isOptionDisabled(getAttrKey('dateValidationPattern', attributes, manifest), dateDisabledOptions)}
									/>
								</div>
							</Popover>
						}
					</Button>
				</div>

				<FancyDivider label={__('Tracking', 'eightshift-forms')} />

				<TextControl
					label={<IconLabel icon={icons.code} label={__('GTM tracking code', 'eightshift-forms')} />}
					value={dateTracking}
					onChange={(value) => setAttributes({ [getAttrKey('dateTracking', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('dateTracking', attributes, manifest), dateDisabledOptions)}
				/>
			</PanelBody>

			<FieldOptionsAdvanced
				{...props('field', attributes)}
			/>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsParentName: dateName,
				})}
			/>
		</>
	);
};
