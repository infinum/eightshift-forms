import React from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { TextControl, PanelBody } from '@wordpress/components';
import { checkAttr, getAttrKey, icons, IconLabel, IconToggle, props } from '@eightshift/frontend-libs/scripts';
import { isOptionDisabled, NameFieldLabel, NameChangeWarning } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import manifest from '../manifest.json';

export const SelectOptionOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const selectOptionLabel = checkAttr('selectOptionLabel', attributes, manifest);
	const selectOptionValue = checkAttr('selectOptionValue', attributes, manifest);
	const selectOptionIsSelected = checkAttr('selectOptionIsSelected', attributes, manifest);
	const selectOptionIsDisabled = checkAttr('selectOptionIsDisabled', attributes, manifest);
	const selectOptionDisabledOptions = checkAttr('selectOptionDisabledOptions', attributes, manifest);

	return (
		<>
			<PanelBody title={__('Option', 'eightshift-forms')}>
				<TextControl
					label={<NameFieldLabel value={selectOptionValue} label={__('Value', 'eightshift-forms')} />}
					help={__('Identifies the field within form submission data. Must be unique.', 'eightshift-forms')}
					value={selectOptionValue}
					onChange={(value) => {
						setIsNameChanged(true);
						setAttributes({ [getAttrKey('selectOptionValue', attributes, manifest)]: value });
					}}
					disabled={isOptionDisabled(getAttrKey('selectOptionValue', attributes, manifest), selectOptionDisabledOptions)}
				/>

				<NameChangeWarning isChanged={isNameChanged} type={'value'} />

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
				/>

				<IconToggle
					icon={icons.cursorDisabled}
					label={__('Disabled', 'eightshift-forms')}
					checked={selectOptionIsDisabled}
					onChange={(value) => setAttributes({ [getAttrKey('selectOptionIsDisabled', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('selectOptionIsDisabled', attributes, manifest), selectOptionDisabledOptions)}
				/>
			</PanelBody>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: selectOptionValue,
				})}
			/>
		</>
	);
};
