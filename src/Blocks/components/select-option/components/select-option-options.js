import React from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { icons } from '@eightshift/ui-components/icons';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { isOptionDisabled, NameField } from './../../utils';
import { ContainerPanel, InputField, Toggle, Spacer } from '@eightshift/ui-components';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import manifest from '../manifest.json';
import { Notice } from '@eightshift/ui-components';

export const SelectOptionOptions = (attributes) => {
	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const selectOptionLabel = checkAttr('selectOptionLabel', attributes, manifest);
	const selectOptionValue = checkAttr('selectOptionValue', attributes, manifest);
	const selectOptionIsSelected = checkAttr('selectOptionIsSelected', attributes, manifest);
	const selectOptionIsDisabled = checkAttr('selectOptionIsDisabled', attributes, manifest);
	const selectOptionIsHidden = checkAttr('selectOptionIsHidden', attributes, manifest);
	const selectOptionDisabledOptions = checkAttr('selectOptionDisabledOptions', attributes, manifest);

	return (
		<ContainerPanel>
			<Spacer
				border
				icon={icons.options}
				text={__('General', 'eightshift-forms')}
			/>

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

			<InputField
				type='multiline'
				placeholder={__('Enter label', 'eightshift-forms')}
				value={selectOptionLabel}
				onChange={(value) => setAttributes({ [getAttrKey('selectOptionLabel', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('selectOptionLabel', attributes, manifest), selectOptionDisabledOptions)}
			/>

			{selectOptionLabel === '' && (
				<Notice
					label={__('Empty or missing label might impact accessibility!', 'eightshift-forms')}
					icon={icons.a11yWarning}
					type='warning'
				/>
			)}

			<Spacer
				border
				icon={icons.tools}
				text={__('Advanced', 'eightshift-forms')}
			/>

			<Toggle
				icon={icons.checkSquare}
				label={__('Selected', 'eightshift-forms')}
				checked={selectOptionIsSelected}
				onChange={(value) => setAttributes({ [getAttrKey('selectOptionIsSelected', attributes, manifest)]: value })}
				disabled={isOptionDisabled(
					getAttrKey('selectOptionIsSelected', attributes, manifest),
					selectOptionDisabledOptions,
				)}
			/>

			<Toggle
				icon={icons.cursorDisabled}
				label={__('Disabled', 'eightshift-forms')}
				checked={selectOptionIsDisabled}
				onChange={(value) => setAttributes({ [getAttrKey('selectOptionIsDisabled', attributes, manifest)]: value })}
				disabled={isOptionDisabled(
					getAttrKey('selectOptionIsDisabled', attributes, manifest),
					selectOptionDisabledOptions,
				)}
			/>

			<Toggle
				icon={icons.hide}
				label={__('Hidden', 'eightshift-forms')}
				checked={selectOptionIsHidden}
				onChange={(value) => setAttributes({ [getAttrKey('selectOptionIsHidden', attributes, manifest)]: value })}
				disabled={isOptionDisabled(
					getAttrKey('selectOptionIsHidden', attributes, manifest),
					selectOptionDisabledOptions,
				)}
			/>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: selectOptionValue,
					conditionalTagsIsHidden: selectOptionIsHidden,
				})}
			/>
		</ContainerPanel>
	);
};
