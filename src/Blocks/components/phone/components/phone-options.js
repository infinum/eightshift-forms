/* global esFormsLocalization */

import React, { useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';
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
	CustomSelect,
	BlockIcon
} from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../field/components/field-options';
import { FieldOptionsAdvanced } from '../../field/components/field-options-advanced';
import manifest from '../manifest.json';
import { isOptionDisabled } from '../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';

export const PhoneOptions = (attributes) => {
	const {
		setAttributes,
		title = __('Phone', 'eightshift-forms'),
	} = attributes;

	const phoneName = checkAttr('phoneName', attributes, manifest);
	const phoneValue = checkAttr('phoneValue', attributes, manifest);
	const phonePlaceholder = checkAttr('phonePlaceholder', attributes, manifest);
	const phoneIsDisabled = checkAttr('phoneIsDisabled', attributes, manifest);
	const phoneIsReadOnly = checkAttr('phoneIsReadOnly', attributes, manifest);
	const phoneIsRequired = checkAttr('phoneIsRequired', attributes, manifest);
	const phoneTracking = checkAttr('phoneTracking', attributes, manifest);
	const phoneValidationPattern = checkAttr('phoneValidationPattern', attributes, manifest);
	const phoneDisabledOptions = checkAttr('phoneDisabledOptions', attributes, manifest);
	const phoneDatasetUsed = checkAttr('phoneDatasetUsed', attributes, manifest);
	const phoneSelectedValue = checkAttr('phoneSelectedValue', attributes, manifest);
	const phoneUseSearch = checkAttr('phoneUseSearch', attributes, manifest);


	const [showValidation, setShowValidation] = useState(false);
	const [dataSet, setDataSet] = useState([]);

	let phoneValidationPatternOptions = [];

	if (typeof esFormsLocalization !== 'undefined' && isArray(esFormsLocalization?.validationPatternsOptions)) {
		phoneValidationPatternOptions = esFormsLocalization.validationPatternsOptions;
	}

	useEffect( () => {
		apiFetch({ path:
			`${esFormsLocalization.restPrefixProject}${esFormsLocalization.restRoutes.countryDataset}` }).then((response) => {
				if (response.code === 200) {
				setDataSet(response.data);
			}
		});

	}, []);

	return (
		<>
			<PanelBody title={title}>
				<FieldOptions
					{...props('field', attributes)}
				/>

				<TextControl
					label={<IconLabel icon={icons.fieldPlaceholder} label={__('Placeholder', 'eightshift-forms')} />}
					help={__('Shown when the field is empty', 'eightshift-forms')}
					value={phonePlaceholder}
					onChange={(value) => setAttributes({ [getAttrKey('phonePlaceholder', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('phonePlaceholder', attributes, manifest), phoneDisabledOptions)}
				/>

				<FancyDivider label={__('Advanced', 'eightshift-forms')} />

				<TextControl
					label={<IconLabel icon={icons.fieldName} label={__('Name', 'eightshift-forms')} />}
					help={__('Should be unique! Used to identify the field within form submission data. If not set, a random name will be generated.', 'eightshift-forms')}
					value={phoneName}
					onChange={(value) => setAttributes({ [getAttrKey('phoneName', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('phoneName', attributes, manifest), phoneDisabledOptions)}
				/>

				<TextControl
					label={<IconLabel icon={icons.fieldValue} label={__('Initial value', 'eightshift-forms')} />}
					value={phoneValue}
					onChange={(value) => setAttributes({ [getAttrKey('phoneValue', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('phoneValue', attributes, manifest), phoneDisabledOptions)}
				/>

				<div className='es-h-spaced'>
					<Button
						icon={icons.fieldReadonly}
						isPressed={phoneIsReadOnly}
						onClick={() => setAttributes({ [getAttrKey('phoneIsReadOnly', attributes, manifest)]: !phoneIsReadOnly })}
						disabled={isOptionDisabled(getAttrKey('phoneIsReadOnly', attributes, manifest), phoneDisabledOptions)}
					>
						{__('Read-only', 'eightshift-forms')}
					</Button>


					<Button
						icon={icons.fieldDisabled}
						isPressed={phoneIsDisabled}
						onClick={() => setAttributes({ [getAttrKey('phoneIsDisabled', attributes, manifest)]: !phoneIsDisabled })}
						disabled={isOptionDisabled(getAttrKey('phoneIsDisabled', attributes, manifest), phoneDisabledOptions)}
					>
						{__('Disabled', 'eightshift-forms')}
					</Button>
				</div>

				<div className='es-h-spaced'>
					<Button
						icon={icons.fieldRequired}
						isPressed={phoneUseSearch}
						onClick={() => setAttributes({ [getAttrKey('phoneUseSearch', attributes, manifest)]: !phoneUseSearch })}
						disabled={isOptionDisabled(getAttrKey('phoneUseSearch', attributes, manifest), phoneDisabledOptions)}
					>
						{__('Allow search', 'eightshift-forms')}
					</Button>
				</div>

				<FancyDivider label={__('Data', 'eightshift-forms')} />

				<CustomSelect
					label={<IconLabel icon={<BlockIcon iconName='esf-form-picker' />} label={__('Select a preselected value', 'eightshift-forms')} />}
					help={__('If you can\'t find a form, start typing its name while the dropdown is open.', 'eightshift-forms')}
					value={phoneSelectedValue}
					options={dataSet?.codes}
					onChange={(value) => setAttributes({ [getAttrKey('phoneSelectedValue', attributes, manifest)]: value })}
					isClearable={false}
					cacheOptions={false}
					reFetchOnSearch={true}
					multiple={false}
					closeMenuOnSelect={true}
					simpleValue
				/>

				{dataSet?.items?.length > 1 &&
					<CustomSelect
						label={<IconLabel icon={<BlockIcon iconName='esf-form-picker' />} label={__('Select a data source to display', 'eightshift-forms')} />}
						help={__('If you can\'t find a form, start typing its name while the dropdown is open.', 'eightshift-forms')}
						value={phoneDatasetUsed}
						options={dataSet?.items}
						onChange={(value) => setAttributes({ [getAttrKey('phoneDatasetUsed', attributes, manifest)]: value })}
						isClearable={false}
						cacheOptions={false}
						reFetchOnSearch={false}
						multiple={false}
						closeMenuOnSelect={true}
						simpleValue
					/>
				}

				<FancyDivider label={__('Validation', 'eightshift-forms')} />

				<div className='es-h-spaced-wrap'>
					<Button
						icon={icons.fieldRequired}
						isPressed={phoneIsRequired}
						onClick={() => setAttributes({ [getAttrKey('phoneIsRequired', attributes, manifest)]: !phoneIsRequired })}
						disabled={isOptionDisabled(getAttrKey('phoneIsRequired', attributes, manifest), phoneDisabledOptions)}
					>
						{__('Required', 'eightshift-forms')}
					</Button>

					<Button
						icon={icons.regex}
						isPressed={phoneValidationPattern?.length > 0}
						onClick={() => setShowValidation(true)}
					>
						{__('Pattern validation', 'eightshift-forms')}

						{showValidation &&
							<Popover noArrow={false} onClose={() => setShowValidation(false)}>
								<div className='es-popover-content'>
									<SimpleVerticalSingleSelect
										label={__('Validation pattern', 'eightshift-forms')}
										options={phoneValidationPatternOptions.map(({ label, value }) => ({
											onClick: () => setAttributes({ [getAttrKey('phoneValidationPattern', attributes, manifest)]: value }),
											label: label,
											isActive: phoneValidationPattern === value,
										}))}
										disabled={isOptionDisabled(getAttrKey('phoneValidationPattern', attributes, manifest), phoneDisabledOptions)}
									/>
								</div>
							</Popover>
						}
					</Button>
				</div>

				<FancyDivider label={__('Tracking', 'eightshift-forms')} />

				<TextControl
					label={<IconLabel icon={icons.code} label={__('GTM tracking code', 'eightshift-forms')} />}
					value={phoneTracking}
					onChange={(value) => setAttributes({ [getAttrKey('phoneTracking', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('phoneTracking', attributes, manifest), phoneDisabledOptions)}
				/>
			</PanelBody>

			<FieldOptionsAdvanced
				{...props('field', attributes)}
			/>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes)}
			/>
		</>
	);
};