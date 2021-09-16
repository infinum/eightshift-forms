import React from 'react';
import { TextControl, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const ChoiceOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const choiceLabel = checkAttr('choiceLabel', attributes, manifest);
	const choiceValue = checkAttr('choiceValue', attributes, manifest);
	const choiceName = checkAttr('choiceName', attributes, manifest);
	const choiceIsChecked = checkAttr('choiceIsChecked', attributes, manifest);
	const choiceIsDisabled = checkAttr('choiceIsDisabled', attributes, manifest);
	const choiceIsReadOnly = checkAttr('choiceIsReadOnly', attributes, manifest);

	return (
		<>
			<TextControl
				label={__('Label', 'eightshift-forms')}
				value={choiceLabel}
				onChange={(value) => setAttributes({ [getAttrKey('choiceLabel', attributes, manifest)]: value })}
			/>

			<TextControl
				label={__('Value', 'eightshift-forms')}
				value={choiceValue}
				onChange={(value) => setAttributes({ [getAttrKey('choiceValue', attributes, manifest)]: value })}
			/>

			<TextControl
				label={__('Name', 'eightshift-forms')}
				value={choiceName}
				onChange={(value) => setAttributes({ [getAttrKey('choiceName', attributes, manifest)]: value })}
			/>

			<ToggleControl
				label={__('Is Checked', 'eightshift-forms')}
				checked={choiceIsChecked}
				onChange={(value) => setAttributes({ [getAttrKey('choiceIsChecked', attributes, manifest)]: value })}
			/>

			<ToggleControl
				label={__('Is Disabled', 'eightshift-forms')}
				checked={choiceIsDisabled}
				onChange={(value) => setAttributes({ [getAttrKey('choiceIsDisabled', attributes, manifest)]: value })}
			/>

			<ToggleControl
				label={__('Is Read Only', 'eightshift-forms')}
				checked={choiceIsReadOnly}
				onChange={(value) => setAttributes({ [getAttrKey('choiceIsReadOnly', attributes, manifest)]: value })}
			/>
		</>
	);
};
