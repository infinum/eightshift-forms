import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { TextControl, PanelBody, Button } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	props,
	Section,
	IconToggle,
	STORE_NAME, 
	Select,
	Control,
	NumberPicker,
} from '@eightshift/frontend-libs/scripts';
import { FieldOptions, FieldOptionsMore, FieldOptionsLayout, FieldOptionsVisibility } from '../../field/components/field-options';
import { isOptionDisabled, NameField } from '../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';

export const CountryOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('country');

	const {
		options,
	} = manifest;

	const {
		setAttributes,
	} = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const countryName = checkAttr('countryName', attributes, manifest);
	const countryIsDisabled = checkAttr('countryIsDisabled', attributes, manifest);
	const countryIsRequired = checkAttr('countryIsRequired', attributes, manifest);
	const countryTracking = checkAttr('countryTracking', attributes, manifest);
	const countryDisabledOptions = checkAttr('countryDisabledOptions', attributes, manifest);
	const countryUseSearch = checkAttr('countryUseSearch', attributes, manifest);
	const countryPlaceholder = checkAttr('countryPlaceholder', attributes, manifest);
	const countryUseLabelAsPlaceholder = checkAttr('countryUseLabelAsPlaceholder', attributes, manifest);
	const countryValueType = checkAttr('countryValueType', attributes, manifest);
	const countryIsMultiple = checkAttr('countryIsMultiple', attributes, manifest);
	const countryMinCount = checkAttr('countryMinCount', attributes, manifest);
	const countryMaxCount = checkAttr('countryMaxCount', attributes, manifest);
	const countryValue = checkAttr('countryValue', attributes, manifest);

	return (
		<PanelBody title={__('Country', 'eightshift-forms')}>
			<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
				<NameField
					value={countryName}
					attribute={getAttrKey('countryName', attributes, manifest)}
					disabledOptions={countryDisabledOptions}
					setAttributes={setAttributes}
					type={'country'}
					isChanged={isNameChanged}
					setIsChanged={setIsNameChanged}
				/>
			</Section>

			<FieldOptions
				{...props('field', attributes, {
					fieldDisabledOptions: countryDisabledOptions,
				})}
			/>

			<Section icon={icons.fieldPlaceholder} label={__('Placeholder', 'eightshift-forms')}>
				{!countryUseLabelAsPlaceholder &&
					<TextControl
						help={__('Shown when the field is empty', 'eightshift-forms')}
						value={countryPlaceholder}
						onChange={(value) => setAttributes({ [getAttrKey('countryPlaceholder', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('countryPlaceholder', attributes, manifest), countryDisabledOptions)}
						className='es-no-field-spacing'
					/>
				}
				<IconToggle
					icon={icons.fieldPlaceholder}
					label={__('Use label as placeholder', 'eightshift-forms')}
					checked={countryUseLabelAsPlaceholder}
					onChange={(value) => {
						setAttributes({ [getAttrKey('countryPlaceholder', attributes, manifest)]: undefined });
						setAttributes({ [getAttrKey('countryUseLabelAsPlaceholder', attributes, manifest)]: value });
					}}
				/>
			</Section>

			<FieldOptionsLayout
				{...props('field', attributes, {
					fieldDisabledOptions: countryDisabledOptions,
				})}
			/>

			<Section icon={icons.checks} label={__('Validation', 'eightshift-forms')}>
				<IconToggle
					icon={icons.required}
					label={__('Required', 'eightshift-forms')}
					checked={countryIsRequired}
					onChange={(value) => setAttributes({ [getAttrKey('countryIsRequired', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('countryIsRequired', attributes, manifest), countryDisabledOptions)}
					noBottomSpacing
				/>


				{countryIsMultiple &&
					<Control
						icon={icons.range}
						label={__('Number of items', 'eightshift-forms')}
						additionalLabelClasses='es-mb-0!'
					>
						<div className='es-h-spaced es-gap-5!'>
							<div className='es-display-flex es-items-end es-gap-2'>
								<NumberPicker
									label={__('Min', 'eightshift-forms')}
									value={countryMinCount}
									onChange={(value) => setAttributes({ [getAttrKey('countryMinCount', attributes, manifest)]: value })}
									min={options.countryMinCount.min}
									step={options.countryMinCount.step}
									disabled={isOptionDisabled(getAttrKey('countryMinCount', attributes, manifest), countryDisabledOptions)}
									placeholder='–'
									fixedWidth={4}
									noBottomSpacing
								/>

								{countryMinCount > 0 && !isOptionDisabled(getAttrKey('countryMinCount', attributes, manifest), countryDisabledOptions) &&
									<Button
										label={__('Clear', 'eightshift-forms')}
										icon={icons.clear}
										onClick={() => setAttributes({ [getAttrKey('countryMinCount', attributes, manifest)]: undefined })}
										className='es-button-square-32 es-button-icon-24'
										showTooltip
									/>
								}
							</div>

							<div className='es-display-flex es-items-end es-gap-2'>
								<NumberPicker
									label={__('Max', 'eightshift-forms')}
									value={countryMaxCount}
									onChange={(value) => setAttributes({ [getAttrKey('countryMaxCount', attributes, manifest)]: value })}
									min={options.countryMaxCount.min}
									step={options.countryMaxCount.step}
									disabled={isOptionDisabled(getAttrKey('countryMaxCount', attributes, manifest), countryDisabledOptions)}
									placeholder='–'
									fixedWidth={4}
									noBottomSpacing
								/>

								{countryMaxCount > 0 && !isOptionDisabled(getAttrKey('countryMaxCount', attributes, manifest), countryDisabledOptions) &&
									<Button
										label={__('Clear', 'eightshift-forms')}
										icon={icons.clear}
										onClick={() => setAttributes({ [getAttrKey('countryMaxCount', attributes, manifest)]: undefined })}
										className='es-button-square-32 es-button-icon-24'
										showTooltip
									/>
								}
							</div>
						</div>
					</Control>
				}
			</Section>

			<Section icon={icons.tools} label={__('Advanced', 'eightshift-forms')}>
				<TextControl
					label={<IconLabel icon={icons.titleGeneric} label={__('Initial value', 'eightshift-forms')} />}
					value={countryValue}
					onChange={(value) => setAttributes({ [getAttrKey('countryValue', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('countryValue', attributes, manifest), countryDisabledOptions)}
					help={__('Initial value of the field. This value depends on the value type.', 'eightshift-forms')}
				/>

				<Select
					icon={icons.migrationAlt}
					value={countryValueType}
					onChange={(value) => setAttributes({[getAttrKey('countryValueType', attributes, manifest)]: value})}
					label={__('Value type', 'eightshift-forms')}
					subtitle={__('Determine whether to send the value as a country code, number or (un)localized name to the integration.', 'eightshift-forms')}
					options={[
						{ value: 'countryCode', label: __('Country code', 'eightshift-forms')},
						{ value: 'countryName', label: __('Localized country name (site locale)', 'eightshift-forms')},
						{ value: 'countryUnlocalizedName', label: __('Country name in English', 'eightshift-forms')},
						{ value: 'countryNumber', label: __('Country phone number prefix', 'eightshift-forms')},
					]}
					simpleValue
					noBottomSpacing
				/>

				<FieldOptionsVisibility
					{...props('field', attributes, {
						fieldDisabledOptions: countryDisabledOptions,
					})}
				/>

				<IconToggle
					icon={icons.cursorDisabled}
					label={__('Disabled', 'eightshift-forms')}
					checked={countryIsDisabled}
					onChange={(value) => setAttributes({ [getAttrKey('countryIsDisabled', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('countryIsDisabled', attributes, manifest), countryDisabledOptions)}
				/>

				<IconToggle
					icon={icons.search}
					label={__('Search', 'eightshift-forms')}
					checked={countryUseSearch}
					onChange={(value) => setAttributes({ [getAttrKey('countryUseSearch', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('countryUseSearch', attributes, manifest), countryDisabledOptions)}
				/>

				<IconToggle
					icon={icons.files}
					label={__('Allow multi selection', 'eightshift-forms')}
					checked={countryIsMultiple}
					onChange={(value) => {
						setAttributes({ [getAttrKey('countryIsMultiple', attributes, manifest)]: value });
					}}
					disabled={isOptionDisabled(getAttrKey('countryIsMultiple', attributes, manifest), countryDisabledOptions)}
				/>
			</Section>

			<Section icon={icons.alignHorizontalVertical} label={__('Tracking', 'eightshift-forms')} collapsable>
				<TextControl
					label={<IconLabel icon={icons.googleTagManager} label={__('GTM tracking code', 'eightshift-forms')} />}
					value={countryTracking}
					onChange={(value) => setAttributes({ [getAttrKey('countryTracking', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('countryTracking', attributes, manifest), countryDisabledOptions)}
					className='es-no-field-spacing'
				/>
			</Section>

			<FieldOptionsMore
				{...props('field', attributes, {
					fieldDisabledOptions: countryDisabledOptions,
				})}
			/>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: countryName,
					conditionalTagsIsHidden: checkAttr('countryFieldHidden', attributes, manifest),
				})}
			/>
		</PanelBody>
	);
};
