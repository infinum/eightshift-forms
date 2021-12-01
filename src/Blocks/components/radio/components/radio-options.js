import React from 'react';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { TextControl, TextareaControl } from '@wordpress/components';
import {
	checkAttr,
	getAttrKey,
	icons,
	ComponentUseToggle,
	IconToggle,
	IconLabel
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const RadioOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const radioLabel = checkAttr('radioLabel', attributes, manifest);
	const radioValue = checkAttr('radioValue', attributes, manifest);
	const radioIsChecked = checkAttr('radioIsChecked', attributes, manifest);
	const radioIsDisabled = checkAttr('radioIsDisabled', attributes, manifest);
	const radioTracking = checkAttr('radioTracking', attributes, manifest);

	const [showAdvanced, setShowAdvanced] = useState(false);

	return (
		<>
			<TextareaControl
				label={<IconLabel icon={icons.fieldLabel} label={__('Label', 'eightshift-forms')} />}
				help={__('Set label used next to the radio.', 'eightshift-forms')}
				value={radioLabel}
				onChange={(value) => setAttributes({ [getAttrKey('radioLabel', attributes, manifest)]: value })}
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
						label={<IconLabel icon={icons.fieldValue} label={__('Value', 'eightshift-forms')} />}
						help={__('Provide value that is going to be used when user clicks on this field.', 'eightshift-forms')}
						value={radioValue}
						onChange={(value) => setAttributes({ [getAttrKey('radioValue', attributes, manifest)]: value })}
					/>

					<TextControl
						label={<IconLabel icon={icons.code} label={__('Tracking Code', 'eightshift-forms')} />}
						help={__('Provide GTM tracking code.', 'eightshift-forms')}
						value={radioTracking}
						onChange={(value) => setAttributes({ [getAttrKey('radioTracking', attributes, manifest)]: value })}
					/>

					<IconToggle
						icon={icons.checkCircle}
						label={__('Is Checked', 'eightshift-forms')}
						checked={radioIsChecked}
						onChange={(value) => setAttributes({ [getAttrKey('radioIsChecked', attributes, manifest)]: value })}
					/>

					<IconToggle
						icon={icons.fieldDisabled}
						label={__('Is Disabled', 'eightshift-forms')}
						checked={radioIsDisabled}
						onChange={(value) => setAttributes({ [getAttrKey('radioIsDisabled', attributes, manifest)]: value })}
					/>
				</>
			}
		</>
	);
};
