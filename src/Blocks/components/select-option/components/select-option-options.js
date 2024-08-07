import React from 'react';
import { useState } from '@wordpress/element';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { PanelBody, TextareaControl } from '@wordpress/components';
import { checkAttr, getAttrKey, icons, IconToggle, props, STORE_NAME, Section } from '@eightshift/frontend-libs/scripts';
import { isOptionDisabled, NameField } from './../../utils';
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
		<PanelBody title={__('Option', 'eightshift-forms')}>
			<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
				<NameField
					value={selectOptionValue}
					attribute={getAttrKey('selectOptionValue', attributes, manifest)}
					disabledOptions={selectOptionDisabledOptions}
					setAttributes={setAttributes}
					type='select-option'
					label={__('Value', 'eightshift-forms')}
					isChanged={isNameChanged}
					setIsChanged={setIsNameChanged}
				/>
			</Section>

			<Section icon={icons.tag} label={__('Label', 'eightshift-forms')}>
				<TextareaControl
					value={selectOptionLabel}
					onChange={(value) => setAttributes({ [getAttrKey('selectOptionLabel', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('selectOptionLabel', attributes, manifest), selectOptionDisabledOptions)}
				/>
			</Section>

			<Section icon={icons.tools} label={__('Advanced', 'eightshift-forms')}>
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
					noBottomSpacing
				/>
			</Section>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: selectOptionValue,
					conditionalTagsIsHidden: selectOptionIsHidden,
				})}
			/>
		</PanelBody>
	);
};
