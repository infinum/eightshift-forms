import React from 'react';
import { __ } from '@wordpress/i18n';
import { TextControl } from '@wordpress/components';
import { checkAttr, getAttrKey, icons, IconLabel, IconToggle, Section } from '@eightshift/frontend-libs/scripts';
import { isOptionDisabled } from './../../utils';
import manifest from '../manifest.json';

export const SelectOptionOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const selectOptionLabel = checkAttr('selectOptionLabel', attributes, manifest);
	const selectOptionValue = checkAttr('selectOptionValue', attributes, manifest);
	const selectOptionIsSelected = checkAttr('selectOptionIsSelected', attributes, manifest);
	const selectOptionIsDisabled = checkAttr('selectOptionIsDisabled', attributes, manifest);
	const selectOptionDisabledOptions = checkAttr('selectOptionDisabledOptions', attributes, manifest);

	return (
		<>
			<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
				<TextControl
					label={<IconLabel icon={icons.textSize} label={__('Option label', 'eightshift-forms')} />}
					value={selectOptionLabel}
					onChange={(value) => setAttributes({ [getAttrKey('selectOptionLabel', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('selectOptionLabel', attributes, manifest), selectOptionDisabledOptions)}
				/>

				<IconToggle
					icon={icons.checkSquare}
					label={__('Selected', 'eightshift-forms')}
					checked={selectOptionIsSelected}
					onChange={(value) => setAttributes({ [getAttrKey('selectOptionIsSelected', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('selectOptionIsSelected', attributes, manifest), selectOptionDisabledOptions)}
					noBottomSpacing
				/>
			</Section>

			<Section icon={icons.tools} label={__('Advanced', 'eightshift-forms')} noBottomSpacing>
				<TextControl
					label={<IconLabel icon={icons.fieldValue} label={__('Value', 'eightshift-forms')} />}
					help={__('Internal value, sent if the option is selected', 'eightshift-forms')}
					value={selectOptionValue}
					onChange={(value) => setAttributes({ [getAttrKey('selectOptionValue', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('selectOptionValue', attributes, manifest), selectOptionDisabledOptions)}
				/>

				<IconToggle
					icon={icons.cursorDisabled}
					label={__('Disabled', 'eightshift-forms')}
					checked={selectOptionIsDisabled}
					onChange={(value) => setAttributes({ [getAttrKey('selectOptionIsDisabled', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('selectOptionIsDisabled', attributes, manifest), selectOptionDisabledOptions)}
					noBottomSpacing
				/>
			</Section>
		</>
	);
};
