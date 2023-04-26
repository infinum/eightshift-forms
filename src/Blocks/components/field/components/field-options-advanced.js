/* global esFormsLocalization */

import React from 'react';
import { __ } from '@wordpress/i18n';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	ResponsiveNumberPicker,
	getDefaultBreakpointNames,
	ucfirst,
	Select,
	Section,
	Collapsable,
} from '@eightshift/frontend-libs/scripts';
import { TextControl } from '@wordpress/components';
import manifest from '../manifest.json';
import { isObject } from 'lodash';

export const FieldPanel = (props) => {
	const {
		attributes,
		setAttributes,
		fieldManifest: {
			responsiveAttributes: {
				fieldWidth,
			},
			options,
		},
		children,
	} = props;

	return (
		<Collapsable icon={icons.moreH} label={__('More options', 'eightshift-forms')} noBottomSpacing>
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

				additionalProps={{ fixedWidth: 2 }}
			/>

			{children}
		</Collapsable>
	);
};

export const FieldOptionsAdvanced = (attributes) => {
	const {
		blockName,
		setAttributes,
	} = attributes;

	const fieldBeforeContent = checkAttr('fieldBeforeContent', attributes, manifest);
	const fieldAfterContent = checkAttr('fieldAfterContent', attributes, manifest);
	const fieldStyle = checkAttr('fieldStyle', attributes, manifest);

	let fieldStyleOptions = [];

	if (typeof esFormsLocalization !== 'undefined' && isObject(esFormsLocalization?.fieldBlockStyleOptions)) {
		fieldStyleOptions = esFormsLocalization.fieldBlockStyleOptions[blockName];
	}

	return (
		<FieldPanel
			fieldManifest={manifest}
			attributes={attributes}
			setAttributes={setAttributes}
		>
			<>
				{fieldStyleOptions &&
					<Select
						inlineLabel
						icon={icons.color}
						label={__('Style', 'eightshift-forms')}
						value={fieldStyle}
						options={fieldStyleOptions}
						onChange={(value) => setAttributes({ [getAttrKey('fieldStyle', attributes, manifest)]: value })}
						additionalSelectClasses='es-w-40'
						simpleValue
					/>
				}

				<Section icon={icons.textAbc} label={__('Additional content ', 'eightshift-forms')} noBottomSpacing>
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
				</Section>
			</>
		</FieldPanel>
	);
};
