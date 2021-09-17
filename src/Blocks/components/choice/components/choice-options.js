import React from 'react';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { TextControl, ToggleControl } from '@wordpress/components';
import { checkAttr, getAttrKey, icons } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const ChoiceOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const choiceLabel = checkAttr('choiceLabel', attributes, manifest);
	const choiceName = checkAttr('choiceName', attributes, manifest);
	const choiceIsChecked = checkAttr('choiceIsChecked', attributes, manifest);
	const choiceIsDisabled = checkAttr('choiceIsDisabled', attributes, manifest);
	const choiceIsReadOnly = checkAttr('choiceIsReadOnly', attributes, manifest);
	const choiceIsRequired = checkAttr('choiceIsRequired', attributes, manifest);
	const choiceTracking = checkAttr('choiceTracking', attributes, manifest);

	const [showAdvanced, setShowAdvanced] = useState(false);

	return (
		<>
			<TextControl
				label={__('Label', 'eightshift-forms')}
				value={choiceLabel}
				onChange={(value) => setAttributes({ [getAttrKey('choiceLabel', attributes, manifest)]: value })}
			/>

			<TextControl
				label={__('Name', 'eightshift-forms')}
				value={choiceName}
				onChange={(value) => setAttributes({ [getAttrKey('choiceName', attributes, manifest)]: value })}
			/>

			<ToggleControl
				label={__('Show advanced options', 'eightshift-forms')}
				checked={showAdvanced}
				onChange={() => setShowAdvanced(!showAdvanced)}
			/>

			{showAdvanced &&
				<>
					<TextControl
						label={__('Tracking code', 'eightshift-forms')}
						value={choiceTracking}
						onChange={(value) => setAttributes({ [getAttrKey('choiceTracking', attributes, manifest)]: value })}
					/>

					<ToggleControl
						icon={icons.play}
						label={__('Is Checked', 'eightshift-forms')}
						checked={choiceIsChecked}
						onChange={(value) => setAttributes({ [getAttrKey('choiceIsChecked', attributes, manifest)]: value })}
					/>

					<ToggleControl
						icon={icons.play}
						label={__('Is Disabled', 'eightshift-forms')}
						checked={choiceIsDisabled}
						onChange={(value) => setAttributes({ [getAttrKey('choiceIsDisabled', attributes, manifest)]: value })}
					/>

					<ToggleControl
						icon={icons.play}
						label={__('Is Read Only', 'eightshift-forms')}
						checked={choiceIsReadOnly}
						onChange={(value) => setAttributes({ [getAttrKey('choiceIsReadOnly', attributes, manifest)]: value })}
					/>

					<ToggleControl
						icon={icons.play}
						label={__('Is Required', 'eightshift-forms')}
						checked={choiceIsRequired}
						onChange={(value) => setAttributes({ [getAttrKey('choiceIsRequired', attributes, manifest)]: value })}
					/>
				</>
			}
		</>
	);
};
