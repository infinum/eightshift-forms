/* global esFormsLocalization */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { TextareaControl, TextControl } from '@wordpress/components';
import { isObject } from 'lodash';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	IconToggle,
	AnimatedContentVisibility,
	STORE_NAME,
	Section,
	ResponsiveNumberPicker,
	getDefaultBreakpointNames,
	ucfirst,
	MultiSelect,
} from '@eightshift/frontend-libs/scripts';
import { isOptionDisabled } from '../../utils';

export const FieldOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('field');

	const {
		setAttributes,

		showFieldLabel = true,

		additionalControls,
	} = attributes;

	const fieldLabel = checkAttr('fieldLabel', attributes, manifest);
	const fieldHideLabel = checkAttr('fieldHideLabel', attributes, manifest);

	return (
		<>
			{showFieldLabel &&
				<Section icon={icons.tag} label={__('Label', 'eightshift-forms')}>
					<IconToggle
						label={__('Use label', 'eightshift-forms')}
						checked={!fieldHideLabel}
						onChange={(value) => setAttributes({ [getAttrKey('fieldHideLabel', attributes, manifest)]: !value })}
						reducedBottomSpacing
					/>

					{!fieldHideLabel &&
						<TextareaControl
							value={fieldLabel}
							onChange={(value) => setAttributes({ [getAttrKey('fieldLabel', attributes, manifest)]: value })}
							disabled={fieldHideLabel}
						/>
					}

					<AnimatedContentVisibility showIf={fieldHideLabel}>
						<IconLabel label={__('Might impact accessibility', 'eightshift-forms')} icon={icons.a11yWarning} additionalClasses='es-nested-color-yellow-500! es-line-h-1 es-color-cool-gray-500 es-mb-5' standalone />
					</AnimatedContentVisibility>
				</Section>
			}

			{additionalControls}
		</>
	);
};

export const FieldOptionsLayout = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('field');

	const {
		responsiveAttributes: {
			fieldWidth,
		},
		options,
	} = manifest;

	const {
		blockName,
		setAttributes,
	} = attributes;

	const fieldStyle = checkAttr('fieldStyle', attributes, manifest);

	let fieldStyleOptions = [];

	if (typeof esFormsLocalization !== 'undefined' && isObject(esFormsLocalization?.fieldBlockStyleOptions)) {
		fieldStyleOptions = esFormsLocalization.fieldBlockStyleOptions[blockName];
	}

	return (
		<Section
			icon={icons.containerSpacing}
			label={__('Layout', 'eightshift-forms')}
		>
			<ResponsiveNumberPicker
				value={getDefaultBreakpointNames().reduce((all, current) => {
					return {
						...all,
						[current]: checkAttr(fieldWidth[current], attributes, manifest, true),
					};
				}, {})}
				onChange={(value) => {
					const newData = Object.entries(value).reduce((all, [breakpoint, currentValue]) => {
						return {
							...all,
							[getAttrKey(`fieldWidth${ucfirst(breakpoint)}`, attributes, manifest)]: currentValue,
						};
					}, {});

					setAttributes(newData);
				}}

				min={options.fieldWidth.min}
				max={options.fieldWidth.max}
				step={options.fieldWidth.step}

				icon={icons.width}
				label={__('Width', 'eightshift-forms')}

				additionalProps={{ fixedWidth: 4 }}
			/>

			{fieldStyleOptions?.length > 0 &&
				<MultiSelect
					icon={icons.paletteColor}
					label={__('Style', 'eightshift-forms')}
					value={fieldStyle}
					options={fieldStyleOptions}
					onChange={(value) => setAttributes({ [getAttrKey('fieldStyle', attributes, manifest)]: value })}
					simpleValue
					additionalSelectClasses='es-w-50'
					inlineLabel
				/>
			}
		</Section>
	);
};

export const FieldOptionsMore = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('field');

	const {
		setAttributes,
	} = attributes;

	const fieldHelp = checkAttr('fieldHelp', attributes, manifest);
	const fieldBeforeContent = checkAttr('fieldBeforeContent', attributes, manifest);
	const fieldAfterContent = checkAttr('fieldAfterContent', attributes, manifest);

	return (
		<Section
			icon={icons.moreH}
			label={__('More options', 'eightshift-forms')}
			noBottomSpacing
			collapsable
		>
			<>
				<TextControl
					label={<IconLabel icon={icons.help} label={__('Help text', 'eightshift-forms')} />}
					value={fieldHelp}
					onChange={(value) => setAttributes({ [getAttrKey('fieldHelp', attributes, manifest)]: value })}
				/>

				<TextControl
					label={<IconLabel icon={icons.fieldBeforeText} label={__('Below the field label', 'eightshift-forms')} />}
					value={fieldBeforeContent}
					onChange={(value) => setAttributes({ [getAttrKey('fieldBeforeContent', attributes, manifest)]: value })}
				/>

				<TextControl
					label={<IconLabel icon={icons.fieldAfterText} label={__('Above the help text', 'eightshift-forms')} />}
					value={fieldAfterContent}
					onChange={(value) => setAttributes({ [getAttrKey('fieldAfterContent', attributes, manifest)]: value })}
					className='es-no-field-spacing'
				/>
			</>
		</Section>
	);
};

export const FieldOptionsVisibility = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('field');

	const {
		setAttributes,
	} = attributes;

	const fieldHidden = checkAttr('fieldHidden', attributes, manifest);
	const fieldDisabledOptions = checkAttr('fieldDisabledOptions', attributes, manifest);

	return (
		<IconToggle
			icon={icons.hide}
			label={__('Hidden', 'eightshift-forms')}
			checked={fieldHidden}
			onChange={(value) => setAttributes({ [getAttrKey('fieldHidden', attributes, manifest)]: value })}
			disabled={isOptionDisabled(getAttrKey('fieldHidden', attributes, manifest), fieldDisabledOptions)}
		/>
	);
};

