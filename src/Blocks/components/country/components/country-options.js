import React from 'react';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import {
	BaseControl,
	Select,
	NumberPicker,
	ContainerPanel,
	InputField,
	Toggle,
	Spacer,
	ContainerGroup,
} from '@eightshift/ui-components';
import { icons } from '@eightshift/ui-components/icons';
import {
	FieldOptions,
	FieldOptionsMore,
	FieldOptionsLayout,
	FieldOptionsVisibility,
} from '../../field/components/field-options';
import { isOptionDisabled, NameField } from '../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import manifest from '../manifest.json';
import { HStack } from '@eightshift/ui-components';

export const CountryOptions = (attributes) => {
	const { options } = manifest;

	const { setAttributes } = attributes;

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
		<ContainerPanel>
			<Spacer
				border
				icon={icons.options}
				text={__('General', 'eightshift-forms')}
			/>
			<NameField
				value={countryName}
				attribute={getAttrKey('countryName', attributes, manifest)}
				disabledOptions={countryDisabledOptions}
				setAttributes={setAttributes}
				type={'country'}
				isChanged={isNameChanged}
				setIsChanged={setIsNameChanged}
			/>

			<FieldOptions
				{...props('field', attributes, {
					fieldDisabledOptions: countryDisabledOptions,
				})}
			/>

			<ContainerGroup>
				<Toggle
					icon={icons.fieldPlaceholder}
					label={__('Use label as placeholder', 'eightshift-forms')}
					checked={countryUseLabelAsPlaceholder}
					onChange={(value) => {
						setAttributes({ [getAttrKey('countryPlaceholder', attributes, manifest)]: undefined });
						setAttributes({ [getAttrKey('countryUseLabelAsPlaceholder', attributes, manifest)]: value });
					}}
				/>
				{!countryUseLabelAsPlaceholder && (
					<InputField
						placeholder={__('Enter placeholder', 'eightshift-forms')}
						help={__('Shown when the field is empty', 'eightshift-forms')}
						value={countryPlaceholder}
						onChange={(value) => setAttributes({ [getAttrKey('countryPlaceholder', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('countryPlaceholder', attributes, manifest), countryDisabledOptions)}
					/>
				)}
			</ContainerGroup>

			<FieldOptionsLayout
				{...props('field', attributes, {
					fieldDisabledOptions: countryDisabledOptions,
				})}
			/>

			<Spacer
				border
				icon={icons.tools}
				text={__('Advanced', 'eightshift-forms')}
			/>

			<InputField
				icon={icons.titleGeneric}
				label={__('Initial value', 'eightshift-forms')}
				placeholder={__('Enter initial value', 'eightshift-forms')}
				value={countryValue}
				onChange={(value) => setAttributes({ [getAttrKey('countryValue', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('countryValue', attributes, manifest), countryDisabledOptions)}
				help={__(
					'Initial value of the field in country code format (e.g. "hr"). If you want to select multiple countries, separate them with a comma. If geolocation is enabled it will be preselected based on the user\'s location.',
					'eightshift-forms',
				)}
			/>

			<FieldOptionsVisibility
				{...props('field', attributes, {
					fieldDisabledOptions: countryDisabledOptions,
				})}
			/>

			<Toggle
				icon={icons.cursorDisabled}
				label={__('Disabled', 'eightshift-forms')}
				checked={countryIsDisabled}
				onChange={(value) => setAttributes({ [getAttrKey('countryIsDisabled', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('countryIsDisabled', attributes, manifest), countryDisabledOptions)}
			/>

			<Toggle
				icon={icons.search}
				label={__('Search', 'eightshift-forms')}
				checked={countryUseSearch}
				onChange={(value) => setAttributes({ [getAttrKey('countryUseSearch', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('countryUseSearch', attributes, manifest), countryDisabledOptions)}
			/>

			<Toggle
				icon={icons.files}
				label={__('Allow multi selection', 'eightshift-forms')}
				checked={countryIsMultiple}
				onChange={(value) => {
					setAttributes({ [getAttrKey('countryIsMultiple', attributes, manifest)]: value });
				}}
				disabled={isOptionDisabled(getAttrKey('countryIsMultiple', attributes, manifest), countryDisabledOptions)}
			/>

			<Select
				icon={icons.migrationAlt}
				value={countryValueType}
				onChange={(value) => setAttributes({ [getAttrKey('countryValueType', attributes, manifest)]: value })}
				label={__('Output value type', 'eightshift-forms')}
				help={__('Determine which value to send on form submission.', 'eightshift-forms')}
				options={[
					{
						value: 'countryCode',
						label: __('Country code (lowercase)', 'eightshift-forms'),
					},
					{
						value: 'countryCodeUppercase',
						label: __('Country code (uppercase)', 'eightshift-forms'),
					},
					{
						value: 'countryName',
						label: __('Localized country name (site locale)', 'eightshift-forms'),
					},
					{
						value: 'countryUnlocalizedName',
						label: __('Country name in English', 'eightshift-forms'),
					},
				]}
				simpleValue
			/>

			<Spacer
				border
				icon={icons.checks}
				text={__('Validation', 'eightshift-forms')}
			/>

			<Toggle
				icon={icons.fieldRequired}
				label={__('Required', 'eightshift-forms')}
				checked={countryIsRequired}
				onChange={(value) => setAttributes({ [getAttrKey('countryIsRequired', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('countryIsRequired', attributes, manifest), countryDisabledOptions)}
			/>

			{countryIsMultiple && (
				<BaseControl
					icon={icons.range}
					label={__('Number of items', 'eightshift-forms')}
				>
					<HStack>
						<NumberPicker
							aria-label={__('Min', 'eightshift-forms')}
							value={countryMinCount}
							onChange={(value) => setAttributes({ [getAttrKey('countryMinCount', attributes, manifest)]: value })}
							min={options.countryMinCount.min}
							step={options.countryMinCount.step}
							disabled={isOptionDisabled(getAttrKey('countryMinCount', attributes, manifest), countryDisabledOptions)}
							placeholder='–'
							fixedWidth={4}
							prefix={__('Min', 'eightshift-forms')}
						>
							<button
								icon={icons.resetToZero}
								tooltip={__('Reset', 'eightshift-forms')}
								onClick={() => setAttributes({ [getAttrKey('countryMinCount', attributes, manifest)]: undefined })}
								disabled={countryMinCount === 0}
								type='ghost'
							>
								{__('x', 'eightshift-forms')}
							</button>
						</NumberPicker>

						<NumberPicker
							aria-label={__('Max', 'eightshift-forms')}
							value={countryMaxCount}
							onChange={(value) => setAttributes({ [getAttrKey('countryMaxCount', attributes, manifest)]: value })}
							min={options.countryMaxCount.min}
							step={options.countryMaxCount.step}
							disabled={isOptionDisabled(getAttrKey('countryMaxCount', attributes, manifest), countryDisabledOptions)}
							placeholder='–'
							fixedWidth={4}
							prefix={__('Max', 'eightshift-forms')}
						>
							<button
								icon={icons.resetToZero}
								tooltip={__('Reset', 'eightshift-forms')}
								onClick={() => setAttributes({ [getAttrKey('countryMaxCount', attributes, manifest)]: undefined })}
								disabled={countryMaxCount === 0}
								type='ghost'
							>
								{__('x', 'eightshift-forms')}
							</button>
						</NumberPicker>
					</HStack>
				</BaseControl>
			)}

			<Spacer
				border
				icon={icons.alignHorizontalVertical}
				text={__('Tracking', 'eightshift-forms')}
			/>

			<InputField
				icon={icons.googleTagManager}
				label={__('GTM tracking code', 'eightshift-forms')}
				placeholder={__('Enter GTM tracking code', 'eightshift-forms')}
				value={countryTracking}
				onChange={(value) => setAttributes({ [getAttrKey('countryTracking', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('countryTracking', attributes, manifest), countryDisabledOptions)}
			/>

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
		</ContainerPanel>
	);
};
