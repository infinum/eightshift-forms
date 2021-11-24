import React from 'react';
import _ from "lodash";
import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { TextControl, RangeControl } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	Responsive,
	IconToggle
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
	const fieldHideLabel = checkAttr('fieldHideLabel', attributes, manifest);

	return (
		<>
			<TextControl
				label={<IconLabel icon={icons.fieldLabel} label={__('Label', 'eightshift-forms')} />}
				help={__('Set label for your field or field group.', 'eightshift-forms')}
				value={fieldLabel}
				onChange={(value) => setAttributes({ [getAttrKey('fieldLabel', attributes, manifest)]: value })}
			/>

			<IconToggle
				icon={icons.hide}
				label={__('Hide Label', 'eightshift-forms')}
				help={__('Hide label from view. Keep in mind this is not the recommended option because label helps your form be more accessible!', 'eightshift-forms')}
				checked={fieldHideLabel}
				onChange={(value) => setAttributes({ [getAttrKey('fieldHideLabel', attributes, manifest)]: value })}
			/>

			<Responsive
				label={<IconLabel icon={icons.fieldWidth} label={__('Width', 'eightshift-forms')} />}
			>
				{Object.entries(fieldWidth).map(([breakpoint, responsiveAttribute], index) => {
					const { default: defaultWidth } = manifestAttributes[responsiveAttribute];

					return (
						<Fragment key={index}>
							<RangeControl
								label={_.capitalize(breakpoint)}
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
