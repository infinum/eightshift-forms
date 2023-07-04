import React from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { TextControl, PanelBody, Button } from '@wordpress/components';
import { MediaPlaceholder } from '@wordpress/block-editor';
import { checkAttr, getAttrKey, icons, IconLabel, IconToggle, Section, props } from '@eightshift/frontend-libs/scripts';
import { isOptionDisabled, NameFieldLabel, NameChangeWarning } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import manifest from '../manifest.json';

export const CheckboxOptions = (attributes) => {
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

	return (
		<>
			<PanelBody title={__('Checkbox', 'eightshift-forms')}>
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

				<TextControl
					label={<IconLabel icon={icons.tag} label={__('Label', 'eightshift-forms')} />}
					value={checkboxLabel}
					onChange={(value) => setAttributes({ [getAttrKey('checkboxLabel', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('checkboxLabel', attributes, manifest), checkboxDisabledOptions)}
				/>

				<IconToggle
					icon={icons.checkSquare}
					label={__('Checked', 'eightshift-forms')}
					checked={checkboxIsChecked}
					onChange={(value) => setAttributes({ [getAttrKey('checkboxIsChecked', attributes, manifest)]: value })}
				/>

				<IconToggle
					icon={icons.readOnly}
					label={__('Read-only', 'eightshift-forms')}
					checked={checkboxIsReadOnly}
					onChange={(value) => setAttributes({ [getAttrKey('checkboxIsReadOnly', attributes, manifest)]: value })}
				/>

				<IconToggle
					icon={icons.cursorDisabled}
					label={__('Disabled', 'eightshift-forms')}
					checked={checkboxIsDisabled}
					onChange={(value) => setAttributes({ [getAttrKey('checkboxIsDisabled', attributes, manifest)]: value })}
				/>

				<Section
					icon={icons.image}
					label={__('Field icon', 'eightshift-forms')}
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

				<Section icon={icons.alignHorizontalVertical} label={__('Tracking', 'eightshift-forms')} noBottomSpacing>
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
				})}
			/>
		</>
	);
};
