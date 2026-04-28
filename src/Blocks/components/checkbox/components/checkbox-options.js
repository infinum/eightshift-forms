import React from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { MediaPlaceholder } from '@wordpress/block-editor';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { Button, ContainerPanel, InputField, Toggle, Spacer } from '@eightshift/ui-components';
import { a11yWarning, alignHorizontalVertical, checkSquare, cursorDisabled, googleTagManager, hide, options, tools, trash } from '@eightshift/ui-components/icons';
import { isOptionDisabled, NameField } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import manifest from '../manifest.json';
import { Notice } from '@eightshift/ui-components';

export const CheckboxOptions = (attributes) => {
	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const checkboxLabel = checkAttr('checkboxLabel', attributes, manifest);
	const checkboxValue = checkAttr('checkboxValue', attributes, manifest);
	const checkboxIsChecked = checkAttr('checkboxIsChecked', attributes, manifest);
	const checkboxIsDisabled = checkAttr('checkboxIsDisabled', attributes, manifest);
	const checkboxTracking = checkAttr('checkboxTracking', attributes, manifest);
	const checkboxDisabledOptions = checkAttr('checkboxDisabledOptions', attributes, manifest);
	const checkboxIcon = checkAttr('checkboxIcon', attributes, manifest);
	const checkboxHideLabelText = checkAttr('checkboxHideLabelText', attributes, manifest);
	const checkboxIsHidden = checkAttr('checkboxIsHidden', attributes, manifest);

	return (
		<ContainerPanel>
			<Spacer
				border
				icon={options}
				text={__('General', 'eightshift-forms')}
			/>
			<NameField
				value={checkboxValue}
				attribute={getAttrKey('checkboxValue', attributes, manifest)}
				disabledOptions={checkboxDisabledOptions}
				setAttributes={setAttributes}
				label={__('Value', 'eightshift-forms')}
				type='checkbox'
				isChanged={isNameChanged}
				setIsChanged={setIsNameChanged}
			/>

			<Toggle
				label={__('Use label', 'eightshift-forms')}
				checked={!checkboxHideLabelText}
				onChange={(value) => setAttributes({ [getAttrKey('checkboxHideLabelText', attributes, manifest)]: !value })}
			/>

			{!checkboxHideLabelText && (
				<InputField
					type='multiline'
					placeholder={__('Enter label', 'eightshift-forms')}
					value={checkboxLabel}
					onChange={(value) => setAttributes({ [getAttrKey('checkboxLabel', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('checkboxLabel', attributes, manifest), checkboxDisabledOptions)}
				/>
			)}

			{(checkboxHideLabelText || checkboxLabel === '') && (
				<Notice
					label={__('Empty or missing label might impact accessibility!', 'eightshift-forms')}
					icon={a11yWarning}
					type='warning'
				/>
			)}

			<Spacer
				border
				icon={tools}
				text={__('Advanced', 'eightshift-forms')}
			/>
			<Toggle
				icon={checkSquare}
				label={__('Checked', 'eightshift-forms')}
				checked={checkboxIsChecked}
				onChange={(value) => setAttributes({ [getAttrKey('checkboxIsChecked', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('checkboxIsChecked', attributes, manifest), checkboxDisabledOptions)}
			/>

			<Toggle
				icon={cursorDisabled}
				label={__('Disabled', 'eightshift-forms')}
				checked={checkboxIsDisabled}
				onChange={(value) => setAttributes({ [getAttrKey('checkboxIsDisabled', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('checkboxIsDisabled', attributes, manifest), checkboxDisabledOptions)}
			/>

			<Toggle
				icon={hide}
				label={__('Hidden', 'eightshift-forms')}
				checked={checkboxIsHidden}
				onChange={(value) => setAttributes({ [getAttrKey('checkboxIsHidden', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('checkboxIsHidden', attributes, manifest), checkboxDisabledOptions)}
			/>

			{checkboxIcon ? (
				<>
					<img
						src={checkboxIcon}
						alt=''
					/>
					<Button
						onClick={() => {
							setAttributes({ [getAttrKey('checkboxIcon', attributes, manifest)]: undefined });
						}}
						icon={trash}
						type='ghost'
					/>
				</>
			) : (
				<MediaPlaceholder
					accept={'image/*'}
					multiple={false}
					allowedTypes={['image']}
					onSelect={({ url }) => setAttributes({ [getAttrKey('checkboxIcon', attributes, manifest)]: url })}
				/>
			)}

			<Spacer
				border
				icon={alignHorizontalVertical}
				text={__('Tracking', 'eightshift-forms')}
			/>

			<InputField
				icon={googleTagManager}
				label={__('GTM tracking code', 'eightshift-forms')}
				placeholder={__('Enter GTM tracking code', 'eightshift-forms')}
				value={checkboxTracking}
				onChange={(value) => setAttributes({ [getAttrKey('checkboxTracking', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('checkboxTracking', attributes, manifest), checkboxDisabledOptions)}
			/>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: checkboxValue,
					conditionalTagsIsHidden: checkboxIsHidden,
				})}
			/>
		</ContainerPanel>
	);
};
