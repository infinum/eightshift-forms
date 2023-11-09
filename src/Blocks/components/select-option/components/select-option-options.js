import React from 'react';
import { useState } from '@wordpress/element';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { TextControl, PanelBody, TextareaControl } from '@wordpress/components';
import { checkAttr, getAttrKey, icons, IconLabel, IconToggle, props, STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { isOptionDisabled, NameFieldLabel, NameChangeWarning } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';

export const SelectOptionOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('select-option');

	const {
		setAttributes,
	} = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const selectOptionLabel = checkAttr('selectOptionLabel', attributes, manifest);
	const selectOptionValue = checkAttr('selectOptionValue', attributes, manifest);
	const selectOptionIsSelected = checkAttr('selectOptionIsSelected', attributes, manifest);
	const selectOptionIsDisabled = checkAttr('selectOptionIsDisabled', attributes, manifest);
	const selectOptionIsHidden = checkAttr('selectOptionIsHidden', attributes, manifest);
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

				<TextareaControl
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
				<IconToggle
					icon={icons.hide}
					label={__('Hidden', 'eightshift-forms')}
					checked={selectOptionIsHidden}
					onChange={(value) => setAttributes({ [getAttrKey('selectOptionIsHidden', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('selectOptionIsHidden', attributes, manifest), selectOptionDisabledOptions)}
				/>
			</PanelBody>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: selectOptionValue,
					conditionalTagsIsHidden: selectOptionIsHidden,
				})}
			/>
		</>
	);
};
