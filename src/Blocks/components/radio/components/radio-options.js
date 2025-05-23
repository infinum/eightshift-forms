import React from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { MediaPlaceholder } from '@wordpress/block-editor';
import { icons } from '@eightshift/ui-components/icons';
import { checkAttr, getAttrKey, props, STORE_NAME } from '@eightshift/frontend-libs-tailwind/scripts';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import { isOptionDisabled, NameField } from './../../utils';
import { RichLabel, BaseControl, Toggle, AnimatedVisibility, Button, ContainerPanel, InputField } from '@eightshift/ui-components';

export const RadioOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('radio');

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
		<ContainerPanel title={__('Radio button', 'eightshift-forms')}>
			<BaseControl
				icon={icons.options}
				label={__('General', 'eightshift-forms')}
			>
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
			</BaseControl>

			<BaseControl
				icon={icons.tag}
				label={__('Label', 'eightshift-forms')}
			>
				<Toggle
					label={__('Use label', 'eightshift-forms')}
					checked={!radioHideLabelText}
					onChange={(value) => setAttributes({ [getAttrKey('radioHideLabelText', attributes, manifest)]: !value })}
					reducedBottomSpacing
				/>

				{!radioHideLabelText && (
					<InputField
						type={'multiline'}
						value={radioLabel}
						onChange={(value) => setAttributes({ [getAttrKey('radioLabel', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('radioLabel', attributes, manifest), radioDisabledOptions)}
					/>
				)}

				<AnimatedVisibility visible={radioHideLabelText}>
					<RichLabel
						label={__('Might impact accessibility', 'eightshift-forms')}
						icon={icons.a11yWarning}
					/>
				</AnimatedVisibility>
			</BaseControl>

			<BaseControl
				icon={icons.tools}
				label={__('Advanced', 'eightshift-forms')}
			>
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
			</BaseControl>

			<BaseControl
				icon={icons.image}
				label={__('Field icon', 'eightshift-forms')}
				collapsable
			>
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
			</BaseControl>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: radioValue,
					conditionalTagsIsHidden: radioIsHidden,
				})}
			/>
		</ContainerPanel>
	);
};
