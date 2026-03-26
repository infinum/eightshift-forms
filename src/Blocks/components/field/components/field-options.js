/* global esFormsLocalization */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { isObject } from '@eightshift/ui-components/utilities';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { MultiSelect, InputField, Toggle, Spacer, ResponsiveLegacy, Slider, Notice } from '@eightshift/ui-components';
import { icons } from '@eightshift/ui-components/icons';
import { isOptionDisabled, NameField } from '../../utils';
import { ConditionalTagsOptions } from '../../../components/conditional-tags/components/conditional-tags-options';
import manifest from '../manifest.json';
import globalManifest from '../../../manifest.json';

export const FieldOptionsExternalBlocks = ({ attributes, setAttributes }) => {
	const [isNameChanged, setIsNameChanged] = useState(false);

	return (
		<>
			<Spacer
				border
				icon={icons.options}
				text={__('General', 'eightshift-forms')}
			/>

			<NameField
				value={attributes?.fieldName}
				attribute='fieldName'
				setAttributes={setAttributes}
				type='custom field'
				isChanged={isNameChanged}
				setIsChanged={setIsNameChanged}
				isOptional
			/>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes)}
				setAttributes={setAttributes}
				conditionalTagsUse={attributes?.conditionalTagsUse}
				conditionalTagsRules={attributes?.conditionalTagsRules}
				conditionalTagsBlockName={attributes?.fieldName}
				conditionalTagsIsHidden={attributes?.conditionalTagsIsHidden}
			/>
		</>
	);
};

export const FieldOptions = (attributes) => {
	const {
		setAttributes,

		showFieldLabel = true,
		showFieldHideLabel = true,

		additionalControls,
	} = attributes;

	const fieldLabel = checkAttr('fieldLabel', attributes, manifest);
	const fieldHideLabel = checkAttr('fieldHideLabel', attributes, manifest);

	return (
		<>
			{showFieldLabel && (
				<>
					<Spacer
						border
						icon={icons.tag}
						text={__('Label', 'eightshift-forms')}
					/>
					{showFieldHideLabel && (
						<Toggle
							label={__('Use label', 'eightshift-forms')}
							checked={!fieldHideLabel}
							onChange={(value) => setAttributes({ [getAttrKey('fieldHideLabel', attributes, manifest)]: !value })}
						/>
					)}

					{!fieldHideLabel && (
						<InputField
							placeholder={__('Enter label', 'eightshift-forms')}
							type='multiline'
							value={fieldLabel}
							onChange={(value) => setAttributes({ [getAttrKey('fieldLabel', attributes, manifest)]: value })}
							disabled={fieldHideLabel}
						/>
					)}

					{(fieldHideLabel || fieldLabel === '') && (
						<Notice
							label={__('Empty or missing label might impact accessibility!', 'eightshift-forms')}
							icon={icons.a11yWarning}
							type='warning'
						/>
					)}
				</>
			)}

			{additionalControls}
		</>
	);
};

export const FieldOptionsLayout = (attributes) => {
	const {
		responsiveAttributes: { fieldWidth },
		options,
	} = manifest;

	const { blockName, setAttributes } = attributes;

	const fieldStyle = checkAttr('fieldStyle', attributes, manifest);

	let fieldStyleOptions = [];

	if (typeof esFormsLocalization !== 'undefined' && isObject(esFormsLocalization?.fieldBlockStyleOptions)) {
		fieldStyleOptions = esFormsLocalization.fieldBlockStyleOptions[blockName];
	}

	return (
		<>
			<Spacer
				border
				icon={icons.containerSpacing}
				text={__('Layout', 'eightshift-forms')}
			/>
			<ResponsiveLegacy
				{...getResponsiveLegacyData(manifest.responsiveAttributes.fieldWidth, attributes, manifest, setAttributes)}
				breakpointData={globalManifest.globalVariables.breakpoints}
				icon={icons.width}
				label={__('Width', 'eightshift-forms')}
			>
				{({ currentValue, handleChange }) => (
					<Slider
						aria-label={__('Width', 'eightshift-forms')}
						value={currentValue ?? 0}
						onChange={handleChange}
						min={manifest.options.fieldWidth.min}
						max={manifest.options.fieldWidth.max}
						step={manifest.options.fieldWidth.step}
						after={currentValue}
					/>
				)}
			</ResponsiveLegacy>

			{fieldStyleOptions?.length > 0 && (
				<MultiSelect
					icon={icons.paletteColor}
					label={__('Style', 'eightshift-forms')}
					value={fieldStyle}
					options={fieldStyleOptions}
					onChange={(value) => setAttributes({ [getAttrKey('fieldStyle', attributes, manifest)]: value })}
					simpleValue
				/>
			)}
		</>
	);
};

export const FieldOptionsMore = (attributes) => {
	const { setAttributes } = attributes;

	const fieldHelp = checkAttr('fieldHelp', attributes, manifest);
	const fieldBeforeContent = checkAttr('fieldBeforeContent', attributes, manifest);
	const fieldAfterContent = checkAttr('fieldAfterContent', attributes, manifest);
	const fieldSuffixContent = checkAttr('fieldSuffixContent', attributes, manifest);

	return (
		<>
			<Spacer
				border
				icon={icons.moreH}
				text={__('Content options', 'eightshift-forms')}
			/>
			<>
				<InputField
					icon={icons.help}
					label={__('Help text', 'eightshift-forms')}
					value={fieldHelp}
					onChange={(value) => setAttributes({ [getAttrKey('fieldHelp', attributes, manifest)]: value })}
				/>

				<InputField
					icon={icons.fieldBeforeText}
					label={__('Below the field label', 'eightshift-forms')}
					value={fieldBeforeContent}
					onChange={(value) => setAttributes({ [getAttrKey('fieldBeforeContent', attributes, manifest)]: value })}
				/>

				<InputField
					icon={icons.fieldAfterText}
					label={__('Above the help text', 'eightshift-forms')}
					value={fieldAfterContent}
					onChange={(value) => setAttributes({ [getAttrKey('fieldAfterContent', attributes, manifest)]: value })}
				/>

				<InputField
					icon={icons.fieldAfterText}
					label={__('After field text', 'eightshift-forms')}
					value={fieldSuffixContent}
					onChange={(value) => setAttributes({ [getAttrKey('fieldSuffixContent', attributes, manifest)]: value })}
				/>
			</>
		</>
	);
};

export const FieldOptionsVisibility = (attributes) => {
	const { setAttributes } = attributes;

	const fieldHidden = checkAttr('fieldHidden', attributes, manifest);
	const fieldDisabledOptions = checkAttr('fieldDisabledOptions', attributes, manifest);

	return (
		<Toggle
			icon={icons.hide}
			label={__('Hidden', 'eightshift-forms')}
			checked={fieldHidden}
			onChange={(value) => setAttributes({ [getAttrKey('fieldHidden', attributes, manifest)]: value })}
			disabled={isOptionDisabled(getAttrKey('fieldHidden', attributes, manifest), fieldDisabledOptions)}
		/>
	);
};

/**
 * Get the data for `ResponsiveLegacy` from Eightshift UI components.
 *
 * @param {Object} responsiveAttr - Responsive attribute data, usually from `manifest.responsiveAttributes`.
 * @param {Object} attributes - Component/block attributes.
 * @param {Object} manifest - Component/block manifest.
 * @param {function} setAttributes - The `setAttributes` function.
 *
 * @access public
 * @since 13.0.0
 *
 * @returns Object
 */
export const getResponsiveLegacyData = (responsiveAttr, attributes, manifest, setAttributes) => ({
	attribute: Object.fromEntries(
		Object.entries(responsiveAttr).map(([breakpoint, attrName]) => [
			breakpoint,
			getAttrKey(attrName, attributes, manifest),
		]),
	),
	value: attributes,
	onChange: (attributeName, value) => setAttributes({ [attributeName]: value }),
});
