import React from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { TextControl, PanelBody, Button, TextareaControl } from '@wordpress/components';
import { MediaPlaceholder } from '@wordpress/block-editor';
import {
	checkAttr,
	getAttrKey,
	icons,
	IconLabel,
	IconToggle,
	props,
	Section,
	AnimatedContentVisibility,
	STORE_NAME,
} from '@eightshift/frontend-libs/scripts';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import { isOptionDisabled, NameFieldLabel, NameChangeWarning } from './../../utils';

export const RadioOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('radio');

	const {
		setAttributes,
	} = attributes;

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
		<>
			<PanelBody title={__('Radio button', 'eightshift-forms')}>
				<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
					<TextControl
						label={<NameFieldLabel value={radioValue} label={__('Value', 'eightshift-forms')} />}
						help={__('Identifies the field within form submission data. Must be unique.', 'eightshift-forms')}
						value={radioValue}
						onChange={(value) => {
							setIsNameChanged(true);
							setAttributes({ [getAttrKey('radioValue', attributes, manifest)]: value });
						}}
						disabled={isOptionDisabled(getAttrKey('radioValue', attributes, manifest), radioDisabledOptions)}
					/>

					<NameChangeWarning isChanged={isNameChanged} type={'value'} />
				</Section>

				<Section icon={icons.tag} label={__('Label', 'eightshift-forms')}>
					<IconToggle
						label={__('Use label', 'eightshift-forms')}
						checked={!radioHideLabelText}
						onChange={(value) => setAttributes({ [getAttrKey('radioHideLabelText', attributes, manifest)]: !value })}
						reducedBottomSpacing
					/>

					{!radioHideLabelText &&
						<TextareaControl
							value={radioLabel}
							onChange={(value) => setAttributes({ [getAttrKey('radioLabel', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('radioLabel', attributes, manifest), radioDisabledOptions)}
						/>
					}

					<AnimatedContentVisibility showIf={radioHideLabelText}>
						<IconLabel label={__('Might impact accessibility', 'eightshift-forms')} icon={icons.a11yWarning} additionalClasses='es-nested-color-yellow-500! es-line-h-1 es-color-cool-gray-500 es-mb-5' standalone />
					</AnimatedContentVisibility>
				</Section>

				<Section icon={icons.tools} label={__('Advanced', 'eightshift-forms')}>
					<IconToggle
						icon={icons.checkCircle}
						label={__('Selected', 'eightshift-forms')}
						checked={radioIsChecked}
						onChange={(value) => setAttributes({ [getAttrKey('radioIsChecked', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('radioIsChecked', attributes, manifest), radioDisabledOptions)}
					/>

					<IconToggle
						icon={icons.cursorDisabled}
						label={__('Disabled', 'eightshift-forms')}
						checked={radioIsDisabled}
						onChange={(value) => setAttributes({ [getAttrKey('radioIsDisabled', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('radioIsDisabled', attributes, manifest), radioDisabledOptions)}
					/>

					<IconToggle
						icon={icons.hide}
						label={__('Hidden', 'eightshift-forms')}
						checked={radioIsHidden}
						onChange={(value) => setAttributes({ [getAttrKey('radioIsHidden', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('radioIsHidden', attributes, manifest), radioDisabledOptions)}
						noBottomSpacing
					/>
				</Section>

				<Section
					icon={icons.image}
					label={__('Field icon', 'eightshift-forms')}
					collapsable
				>
					{radioIcon ? 
						<>
							<img src={radioIcon} alt='' />
							<Button
								onClick={() => {
									setAttributes({ [getAttrKey('radioIcon', attributes, manifest)]: undefined });
								}}
								icon={icons.trash}
								className='es-button-icon-24 es-button-square-28 es-rounded-1 es-hover-color-red-500 es-nested-color-current es-transition-colors'
							/>
						</> :
						<MediaPlaceholder
							accept={'image/*'}
							multiple = {false}
							allowedTypes={['image']}
							onSelect={({ url }) => setAttributes({ [getAttrKey('radioIcon', attributes, manifest)]: url })}
						/>
					}
				</Section>
			</PanelBody>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: radioValue,
					conditionalTagsIsHidden: radioIsHidden,
				})}
			/>
		</>
	);
};
