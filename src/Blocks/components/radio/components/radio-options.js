import React from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { MediaPlaceholder } from '@wordpress/block-editor';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { icons } from '@eightshift/ui-components/icons';
import {
	AnimatedVisibility,
	RichLabel,
	Button,
	ContainerPanel,
	InputField,
	Toggle,
	Spacer,
} from '@eightshift/ui-components';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import { isOptionDisabled, NameField } from './../../utils';
import manifest from '../manifest.json';

export const RadioOptions = (attributes) => {
	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const radioLabel = checkAttr('radioLabel', attributes, manifest);
	const radioValue = checkAttr('radioValue', attributes, manifest);
	const radioIsChecked = checkAttr('radioIsChecked', attributes, manifest);
	const radioIsDisabled = checkAttr('radioIsDisabled', attributes, manifest);
	const radioDisabledOptions = checkAttr('radioDisabledOptions', attributes, manifest);
	const radioIcon = checkAttr('radioIcon', attributes, manifest);
	const radioHideLabelText = checkAttr('radioHideLabelText', attributes, manifest);
	const radioIsHidden = checkAttr('radioIsHidden', attributes, manifest);

	return (
		<ContainerPanel>
			<Spacer
				border
				icon={icons.options}
				text={__('General', 'eightshift-forms')}
			/>
			<NameField
				value={radioValue}
				attribute={getAttrKey('radioValue', attributes, manifest)}
				disabledOptions={radioDisabledOptions}
				setAttributes={setAttributes}
				type='radio'
				label={__('Value', 'eightshift-forms')}
				isChanged={isNameChanged}
				setIsChanged={setIsNameChanged}
			/>

			<Toggle
				label={__('Use label', 'eightshift-forms')}
				checked={!radioHideLabelText}
				onChange={(value) => setAttributes({ [getAttrKey('radioHideLabelText', attributes, manifest)]: !value })}
			/>

			{!radioHideLabelText && (
				<InputField
					type='multiline'
					value={radioLabel}
					onChange={(value) => setAttributes({ [getAttrKey('radioLabel', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('radioLabel', attributes, manifest), radioDisabledOptions)}
				/>
			)}

			<AnimatedVisibility visible={!radioHideLabelText}>
				<RichLabel
					label={__('Might impact accessibility', 'eightshift-forms')}
					icon={icons.a11yWarning}
				/>
			</AnimatedVisibility>

			<Spacer
				border
				icon={icons.tools}
				text={__('Advanced', 'eightshift-forms')}
			/>
			<Toggle
				icon={icons.checkCircle}
				label={__('Selected', 'eightshift-forms')}
				checked={radioIsChecked}
				onChange={(value) => setAttributes({ [getAttrKey('radioIsChecked', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('radioIsChecked', attributes, manifest), radioDisabledOptions)}
			/>

			<Toggle
				icon={icons.cursorDisabled}
				label={__('Disabled', 'eightshift-forms')}
				checked={radioIsDisabled}
				onChange={(value) => setAttributes({ [getAttrKey('radioIsDisabled', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('radioIsDisabled', attributes, manifest), radioDisabledOptions)}
			/>

			<Toggle
				icon={icons.hide}
				label={__('Hidden', 'eightshift-forms')}
				checked={radioIsHidden}
				onChange={(value) => setAttributes({ [getAttrKey('radioIsHidden', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('radioIsHidden', attributes, manifest), radioDisabledOptions)}
			/>

			<Spacer
				border
				icon={icons.image}
				text={__('Field icon', 'eightshift-forms')}
			/>

			{radioIcon ? (
				<>
					<img
						src={radioIcon}
						alt=''
					/>
					<Button
						onClick={() => {
							setAttributes({ [getAttrKey('radioIcon', attributes, manifest)]: undefined });
						}}
						icon={icons.trash}
						className='es-button-icon-24 es-button-square-28 es-rounded-1 es-hover-color-red-500 es-nested-color-current es-transition-colors'
					/>
				</>
			) : (
				<MediaPlaceholder
					accept='image/*'
					multiple={false}
					allowedTypes={['image']}
					onSelect={({ url }) => setAttributes({ [getAttrKey('radioIcon', attributes, manifest)]: url })}
				/>
			)}

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: radioValue,
					conditionalTagsIsHidden: radioIsHidden,
				})}
			/>
		</ContainerPanel>
	);
};
