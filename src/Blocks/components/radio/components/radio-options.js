import React from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { TextControl, PanelBody } from '@wordpress/components';
import { checkAttr, getAttrKey, icons, IconLabel, IconToggle, props } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import { isOptionDisabled, NameFieldLabel, NameChangeWarning } from './../../utils';

export const RadioOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const radioLabel = checkAttr('radioLabel', attributes, manifest);
	const radioValue = checkAttr('radioValue', attributes, manifest);
	const radioIsChecked = checkAttr('radioIsChecked', attributes, manifest);
	const radioIsDisabled = checkAttr('radioIsDisabled', attributes, manifest);
	const radioDisabledOptions = checkAttr('radioDisabledOptions', attributes, manifest);

	return (
		<>
			<PanelBody title={__('Radio button', 'eightshift-forms')}>
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

				<TextControl
					label={<IconLabel icon={icons.tag} label={__('Label', 'eightshift-forms')} />}
					value={radioLabel}
					onChange={(value) => setAttributes({ [getAttrKey('radioLabel', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('radioLabel', attributes, manifest), radioDisabledOptions)}
				/>

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
			</PanelBody>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: radioValue,
				})}
			/>
		</>
	);
};
