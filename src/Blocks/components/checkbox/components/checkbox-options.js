import React from 'react';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { TextControl } from '@wordpress/components';
import {
	checkAttr,
	getAttrKey,
	icons,
	ComponentUseToggle,
	IconToggle
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const CheckboxOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const checkboxLabel = checkAttr('checkboxLabel', attributes, manifest);
	const checkboxValue = checkAttr('checkboxValue', attributes, manifest);
	const checkboxIsChecked = checkAttr('checkboxIsChecked', attributes, manifest);
	const checkboxIsDisabled = checkAttr('checkboxIsDisabled', attributes, manifest);
	const checkboxIsReadOnly = checkAttr('checkboxIsReadOnly', attributes, manifest);
	const checkboxTracking = checkAttr('checkboxTracking', attributes, manifest);

	const [showAdvanced, setShowAdvanced] = useState(false);

	return (
		<>
			<TextControl
				label={__('Label', 'eightshift-forms')}
				help={__('Set label used next to the checkbox.', 'eightshift-forms')}
				value={checkboxLabel}
				onChange={(value) => setAttributes({ [getAttrKey('checkboxLabel', attributes, manifest)]: value })}
			/>

			<ComponentUseToggle
				label={__('Show advanced options', 'eightshift-forms')}
				checked={showAdvanced}
				onChange={() => setShowAdvanced(!showAdvanced)}
				showUseToggle={true}
				showLabel={true}
			/>

			{showAdvanced &&
				<>
					<TextControl
						label={__('Value', 'eightshift-forms')}
						help={__('Provide value that is going to be used when user clicks on this field.', 'eightshift-forms')}
						value={checkboxValue}
						onChange={(value) => setAttributes({ [getAttrKey('checkboxValue', attributes, manifest)]: value })}
					/>

					<TextControl
						label={__('Tracking code', 'eightshift-forms')}
						help={__('Provide GTM tracking code.', 'eightshift-forms')}
						value={checkboxTracking}
						onChange={(value) => setAttributes({ [getAttrKey('checkboxTracking', attributes, manifest)]: value })}
					/>

					<IconToggle
						icon={icons.play}
						label={__('Is Checked', 'eightshift-forms')}
						checked={checkboxIsChecked}
						onChange={(value) => setAttributes({ [getAttrKey('checkboxIsChecked', attributes, manifest)]: value })}
					/>

					<IconToggle
						icon={icons.play}
						label={__('Is Disabled', 'eightshift-forms')}
						checked={checkboxIsDisabled}
						onChange={(value) => setAttributes({ [getAttrKey('checkboxIsDisabled', attributes, manifest)]: value })}
					/>

					<IconToggle
						icon={icons.play}
						label={__('Is Read Only', 'eightshift-forms')}
						checked={checkboxIsReadOnly}
						onChange={(value) => setAttributes({ [getAttrKey('checkboxIsReadOnly', attributes, manifest)]: value })}
					/>
				</>
			}
		</>
	);
};
