import React from 'react';
import { useState } from '@wordpress/element';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey, props, STORE_NAME } from '@eightshift/frontend-libs-tailwind/scripts';
import { isOptionDisabled, NameField } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import { icons } from '@eightshift/ui-components/icons';
import { BaseControl, Toggle, ContainerPanel, InputField } from '@eightshift/ui-components';

export const SelectOptionOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('select-option');

	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const selectOptionLabel = checkAttr('selectOptionLabel', attributes, manifest);
	const selectOptionValue = checkAttr('selectOptionValue', attributes, manifest);
	const selectOptionIsSelected = checkAttr('selectOptionIsSelected', attributes, manifest);
	const selectOptionIsDisabled = checkAttr('selectOptionIsDisabled', attributes, manifest);
	const selectOptionIsHidden = checkAttr('selectOptionIsHidden', attributes, manifest);
	const selectOptionDisabledOptions = checkAttr('selectOptionDisabledOptions', attributes, manifest);

	return (
		<ContainerPanel title={__('Option', 'eightshift-forms')}>
			<BaseControl
				icon={icons.options}
				label={__('General', 'eightshift-forms')}
			>
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
			</BaseControl>

			<BaseControl
				icon={icons.tag}
				label={__('Label', 'eightshift-forms')}
			>
				<InputField
					type={'multiline'}
					value={selectOptionLabel}
					onChange={(value) => setAttributes({ [getAttrKey('selectOptionLabel', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('selectOptionLabel', attributes, manifest), selectOptionDisabledOptions)}
				/>
			</BaseControl>

			<BaseControl
				icon={icons.tools}
				label={__('Advanced', 'eightshift-forms')}
			>
				<Toggle
					icon={icons.checkSquare}
					label={__('Selected', 'eightshift-forms')}
					checked={selectOptionIsSelected}
					onChange={(value) => setAttributes({ [getAttrKey('selectOptionIsSelected', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('selectOptionIsSelected', attributes, manifest), selectOptionDisabledOptions)}
				/>

				<Toggle
					icon={icons.cursorDisabled}
					label={__('Disabled', 'eightshift-forms')}
					checked={selectOptionIsDisabled}
					onChange={(value) => setAttributes({ [getAttrKey('selectOptionIsDisabled', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('selectOptionIsDisabled', attributes, manifest), selectOptionDisabledOptions)}
				/>

				<Toggle
					icon={icons.hide}
					label={__('Hidden', 'eightshift-forms')}
					checked={selectOptionIsHidden}
					onChange={(value) => setAttributes({ [getAttrKey('selectOptionIsHidden', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('selectOptionIsHidden', attributes, manifest), selectOptionDisabledOptions)}
				/>
			</BaseControl>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: selectOptionValue,
					conditionalTagsIsHidden: selectOptionIsHidden,
				})}
			/>
		</ContainerPanel>
	);
};
