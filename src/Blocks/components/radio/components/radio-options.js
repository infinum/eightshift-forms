import React from 'react';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { TextControl, ToggleControl } from '@wordpress/components';
import { checkAttr, getAttrKey, icons } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const RadioOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const radioLabel = checkAttr('radioLabel', attributes, manifest);
	const radioIsChecked = checkAttr('radioIsChecked', attributes, manifest);
	const radioIsDisabled = checkAttr('radioIsDisabled', attributes, manifest);
	const radioIsReadOnly = checkAttr('radioIsReadOnly', attributes, manifest);
	const radioTracking = checkAttr('radioTracking', attributes, manifest);

	const [showAdvanced, setShowAdvanced] = useState(false);

	return (
		<>
			<TextControl
				label={__('Label', 'eightshift-forms')}
				value={radioLabel}
				onChange={(value) => setAttributes({ [getAttrKey('radioLabel', attributes, manifest)]: value })}
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
						value={radioTracking}
						onChange={(value) => setAttributes({ [getAttrKey('radioTracking', attributes, manifest)]: value })}
					/>

					<ToggleControl
						icon={icons.play}
						label={__('Is Checked', 'eightshift-forms')}
						checked={radioIsChecked}
						onChange={(value) => setAttributes({ [getAttrKey('radioIsChecked', attributes, manifest)]: value })}
					/>

					<ToggleControl
						icon={icons.play}
						label={__('Is Disabled', 'eightshift-forms')}
						checked={radioIsDisabled}
						onChange={(value) => setAttributes({ [getAttrKey('radioIsDisabled', attributes, manifest)]: value })}
					/>

					<ToggleControl
						icon={icons.play}
						label={__('Is Read Only', 'eightshift-forms')}
						checked={radioIsReadOnly}
						onChange={(value) => setAttributes({ [getAttrKey('radioIsReadOnly', attributes, manifest)]: value })}
					/>
				</>
			}
		</>
	);
};
