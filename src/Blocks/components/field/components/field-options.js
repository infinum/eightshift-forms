import React from 'react';
import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { TextControl, RangeControl } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	Responsive,
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FieldOptions = (attributes) => {
	const {
		attributes: manifestAttributes,
		responsiveAttributes: {
			fieldWidth
		},
		options
	} = manifest;

	const {
		setAttributes,
	} = attributes;

	const fieldLabel = checkAttr('fieldLabel', attributes, manifest);
	const fieldHelp = checkAttr('fieldHelp', attributes, manifest);

	return (
		<>
			<TextControl
				label={<IconLabel icon={icons.id} label={__('Label', 'eightshift-forms')} />}
				help={__('Set label for grouping multiple checkboxes/radios in one field box.', 'eightshift-forms')}
				value={fieldLabel}
				onChange={(value) => setAttributes({ [getAttrKey('fieldLabel', attributes, manifest)]: value })}
			/>

			<TextControl
				label={<IconLabel icon={icons.id} label={__('Help', 'eightshift-forms')} />}
				help={__('Set field help info text.', 'eightshift-forms')}
				value={fieldHelp}
				onChange={(value) => setAttributes({ [getAttrKey('fieldHelp', attributes, manifest)]: value })}
			/>

			<Responsive
				label={<IconLabel icon={icons.Width} label={__('Width', 'eightshift-forms')} />}
			>
				{Object.entries(fieldWidth).map(([breakpoint, responsiveAttribute], index) => {
					const { default: defaultWidth } = manifestAttributes[responsiveAttribute];

					return (
						<Fragment key={index}>
							<RangeControl
								label={breakpoint}
								allowReset={true}
								value={checkAttr(responsiveAttribute, attributes, manifest, true)}
								onChange={(value) => setAttributes({ [getAttrKey(responsiveAttribute, attributes, manifest)]: value })}
								min={options.fieldWidth.min}
								max={options.fieldWidth.max}
								step={options.fieldWidth.step}
								resetFallbackValue={defaultWidth}
							/>
						</Fragment>
					);
				})}
			</Responsive>
		</>
	);
};
