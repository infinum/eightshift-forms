import React from 'react';
import { select } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { TextControl, PanelBody, Button, TextareaControl } from '@wordpress/components';
import { MediaPlaceholder } from '@wordpress/block-editor';
import {
	checkAttr,
	getAttrKey,
	icons,
	IconLabel,
	IconToggle,
	Section,
	props,
	AnimatedContentVisibility,
	STORE_NAME,
} from '@eightshift/frontend-libs/scripts';
import { isOptionDisabled, NameFieldLabel, NameChangeWarning } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';

export const CheckboxOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('checkbox');

	const {
		setAttributes,
	} = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const checkboxLabel = checkAttr('checkboxLabel', attributes, manifest);
	const checkboxValue = checkAttr('checkboxValue', attributes, manifest);
	const checkboxIsChecked = checkAttr('checkboxIsChecked', attributes, manifest);
	const checkboxIsDisabled = checkAttr('checkboxIsDisabled', attributes, manifest);
	const checkboxIsReadOnly = checkAttr('checkboxIsReadOnly', attributes, manifest);
	const checkboxTracking = checkAttr('checkboxTracking', attributes, manifest);
	const checkboxDisabledOptions = checkAttr('checkboxDisabledOptions', attributes, manifest);
	const checkboxIcon = checkAttr('checkboxIcon', attributes, manifest);
	const checkboxHideLabelText = checkAttr('checkboxHideLabelText', attributes, manifest);
	const checkboxIsHidden = checkAttr('checkboxIsHidden', attributes, manifest);

	return (
		<>
			<PanelBody title={__('Checkbox', 'eightshift-forms')}>
				<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
					<TextControl
						label={<NameFieldLabel value={checkboxValue} label={__('Value', 'eightshift-forms')} />}
						help={__('Identifies the field within form submission data. Must be unique.', 'eightshift-forms')}
						value={checkboxValue}
						onChange={(value) => {
							setIsNameChanged(true);
							setAttributes({ [getAttrKey('checkboxValue', attributes, manifest)]: value });
						}}
						disabled={isOptionDisabled(getAttrKey('checkboxValue', attributes, manifest), checkboxDisabledOptions)}
					/>
					<NameChangeWarning isChanged={isNameChanged} type={'value'} />
				</Section>

				<Section icon={icons.tag} label={__('Label', 'eightshift-forms')}>
					<IconToggle
						label={__('Use label', 'eightshift-forms')}
						checked={!checkboxHideLabelText}
						onChange={(value) => setAttributes({ [getAttrKey('checkboxHideLabelText', attributes, manifest)]: !value })}
						reducedBottomSpacing
					/>

					{!checkboxHideLabelText &&
						<TextareaControl
							value={checkboxLabel}
							onChange={(value) => setAttributes({ [getAttrKey('checkboxLabel', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('checkboxLabel', attributes, manifest), checkboxDisabledOptions)}
						/>
					}

					<AnimatedContentVisibility showIf={checkboxHideLabelText}>
						<IconLabel label={__('Might impact accessibility', 'eightshift-forms')} icon={icons.a11yWarning} additionalClasses='es-nested-color-yellow-500! es-line-h-1 es-color-cool-gray-500 es-mb-5' standalone />
					</AnimatedContentVisibility>
				</Section>

				<Section icon={icons.tools} label={__('Advanced', 'eightshift-forms')}>
					<IconToggle
						icon={icons.checkSquare}
						label={__('Checked', 'eightshift-forms')}
						checked={checkboxIsChecked}
						onChange={(value) => setAttributes({ [getAttrKey('checkboxIsChecked', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('checkboxIsChecked', attributes, manifest), checkboxDisabledOptions)}
					/>

					<IconToggle
						icon={icons.readOnly}
						label={__('Read-only', 'eightshift-forms')}
						checked={checkboxIsReadOnly}
						onChange={(value) => setAttributes({ [getAttrKey('checkboxIsReadOnly', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('checkboxIsReadOnly', attributes, manifest), checkboxDisabledOptions)}
					/>

					<IconToggle
						icon={icons.cursorDisabled}
						label={__('Disabled', 'eightshift-forms')}
						checked={checkboxIsDisabled}
						onChange={(value) => setAttributes({ [getAttrKey('checkboxIsDisabled', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('checkboxIsDisabled', attributes, manifest), checkboxDisabledOptions)}
					/>

					<IconToggle
						icon={icons.hide}
						label={__('Hidden', 'eightshift-forms')}
						checked={checkboxIsHidden}
						onChange={(value) => setAttributes({ [getAttrKey('checkboxIsHidden', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('checkboxIsHidden', attributes, manifest), checkboxDisabledOptions)}
						noBottomSpacing
					/>
				</Section>

				<Section
					icon={icons.image}
					label={__('Field icon', 'eightshift-forms')}
					collapsable
				>
					{checkboxIcon ? 
						<>
							<img src={checkboxIcon} alt='' />
							<Button
								onClick={() => {
									setAttributes({ [getAttrKey('checkboxIcon', attributes, manifest)]: undefined });
								}}
								icon={icons.trash}
								className='es-button-icon-24 es-button-square-28 es-rounded-1 es-hover-color-red-500 es-nested-color-current es-transition-colors'
							/>
						</> :
						<MediaPlaceholder
							accept={'image/*'}
							multiple = {false}
							allowedTypes={['image']}
							onSelect={({ url }) => setAttributes({ [getAttrKey('checkboxIcon', attributes, manifest)]: url })}
						/>
					}
				</Section>

				<Section icon={icons.alignHorizontalVertical} label={__('Tracking', 'eightshift-forms')} noBottomSpacing collapsable>
					<TextControl
						label={<IconLabel icon={icons.googleTagManager} label={__('GTM tracking code', 'eightshift-forms')} />}
						value={checkboxTracking}
						onChange={(value) => setAttributes({ [getAttrKey('checkboxTracking', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('checkboxTracking', attributes, manifest), checkboxDisabledOptions)}
						className='es-no-field-spacing'
					/>
				</Section>
			</PanelBody>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: checkboxValue,
					conditionalTagsIsHidden: checkboxIsHidden,
				})}
			/>
		</>
	);
};
