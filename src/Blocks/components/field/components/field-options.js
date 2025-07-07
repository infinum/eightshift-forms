/* global esFormsLocalization */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { select } from '@wordpress/data';
import { checkAttr, getAttrKey, STORE_NAME, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { isOptionDisabled, NameField } from '../../utils';
import { ConditionalTagsOptions } from '../../../components/conditional-tags/components/conditional-tags-options';
import { icons } from '@eightshift/ui-components/icons';
import { isObject } from '@eightshift/ui-components/utilities';
import { AnimatedVisibility, InputField, RichLabel, MultiSelect, BaseControl, Toggle, ResponsiveLegacy, Slider } from '@eightshift/ui-components';
import globalManifest from './../../../manifest.json';

const getResponsiveLegacyData = (responsiveAttr, attributes, manifest, setAttributes) => ({
	attribute: Object.fromEntries(Object.entries(responsiveAttr).map(([breakpoint, attrName]) => [breakpoint, getAttrKey(attrName, attributes, manifest)])),
	value: attributes,
	onChange: (attributeName, value) => setAttributes({ [attributeName]: value }),
});

export const FieldOptionsExternalBlocks = ({ attributes, setAttributes }) => {
	const [isNameChanged, setIsNameChanged] = useState(false);

	return (
		<>
			<BaseControl
				icon={icons.options}
				label={__('General', 'eightshift-forms')}
			>
				<NameField
					value={attributes?.fieldName}
					attribute='fieldName'
					setAttributes={setAttributes}
					type='custom field'
					isChanged={isNameChanged}
					setIsChanged={setIsNameChanged}
					isOptional
				/>
			</BaseControl>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes)}
				setAttributes={setAttributes}
				conditionalTagsUse={attributes?.conditionalTagsUse}
				conditionalTagsRules={attributes?.conditionalTagsRules}
				conditionalTagsBlockName={attributes?.fieldName}
				conditionalTagsIsHidden={attributes?.conditionalTagsIsHidden}
				useCustom
			/>
		</>
	);
};

export const FieldOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('field');

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
				<BaseControl
					icon={icons.tag}
					label={__('Label', 'eightshift-forms')}
				>
					{showFieldHideLabel && (
						<Toggle
							label={__('Use label', 'eightshift-forms')}
							checked={!fieldHideLabel}
							onChange={(value) => setAttributes({ [getAttrKey('fieldHideLabel', attributes, manifest)]: !value })}
							reducedBottomSpacing
						/>
					)}

					{!fieldHideLabel && (
						<InputField
							type={'multiline'}
							value={fieldLabel}
							onChange={(value) => setAttributes({ [getAttrKey('fieldLabel', attributes, manifest)]: value })}
							disabled={fieldHideLabel}
						/>
					)}

					<AnimatedVisibility visible={fieldHideLabel || fieldLabel === ''}>
						<RichLabel
							label={__('Empty or missing label might impact accessibility!', 'eightshift-forms')}
							icon={icons.a11yWarning}
						/>
					</AnimatedVisibility>
				</BaseControl>
			)}

			{additionalControls}
		</>
	);
};

export const FieldOptionsLayout = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('field');

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
		<BaseControl
			icon={icons.containerSpacing}
			label={__('Layout', 'eightshift-forms')}
		>
			<ResponsiveLegacy
				{...getResponsiveLegacyData(manifest.responsiveAttributes.fieldWidth, attributes, manifest, setAttributes)}
				breakpointData={globalManifest.globalVariables.breakpointsLegacy}
				icon={icons.width}
				label={__('Width', 'eightshift-forms')}
			>
				{({ currentValue, handleChange }) => (
					<div className='infinum-editor-res-slider-fix'>
						<Slider
							value={currentValue ?? 0}
							onChange={handleChange}
							min={manifest.options.fieldWidth.min}
							max={manifest.options.fieldWidth.max}
							step={manifest.options.fieldWidth.step}
							after={currentValue}
						/>
					</div>
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
					additionalSelectClasses='es:w-50'
					inline
				/>
			)}
		</BaseControl>
	);
};

export const FieldOptionsMore = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('field');

	const { setAttributes } = attributes;

	const fieldHelp = checkAttr('fieldHelp', attributes, manifest);
	const fieldBeforeContent = checkAttr('fieldBeforeContent', attributes, manifest);
	const fieldAfterContent = checkAttr('fieldAfterContent', attributes, manifest);
	const fieldSuffixContent = checkAttr('fieldSuffixContent', attributes, manifest);

	return (
		<BaseControl
			icon={icons.moreH}
			label={__('More options', 'eightshift-forms')}
			collapsable
		>
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
					className='es:no-field-spacing'
				/>

				<InputField
					icon={icons.fieldAfterText}
					label={__('After field text', 'eightshift-forms')}
					value={fieldSuffixContent}
					onChange={(value) => setAttributes({ [getAttrKey('fieldSuffixContent', attributes, manifest)]: value })}
					className='es:no-field-spacing'
				/>
			</>
		</BaseControl>
	);
};

export const FieldOptionsVisibility = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('field');

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
