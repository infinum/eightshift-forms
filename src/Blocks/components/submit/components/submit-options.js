import React from 'react';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { TextControl, SelectControl, ToggleControl } from '@wordpress/components';
import {
	icons,
	getOption,
	checkAttr,
	getAttrKey,
	IconLabel,
	IconToggle
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const SubmitOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const submitName = checkAttr('submitName', attributes, manifest);
	const submitValue = checkAttr('submitValue', attributes, manifest);
	const submitId = checkAttr('submitId', attributes, manifest);
	const submitType = checkAttr('submitType', attributes, manifest);
	const submitIsDisabled = checkAttr('submitIsDisabled', attributes, manifest);
	const submitTracking = checkAttr('submitTracking', attributes, manifest);

	const [showAdvanced, setShowAdvanced] = useState(false);

	return (
		<>
			<TextControl
				label={<IconLabel icon={icons.id} label={__('Name', 'eightshift-forms')} />}
				value={submitName}
				onChange={(value) => setAttributes({ [getAttrKey('submitName', attributes, manifest)]: value })}
			/>

			<TextControl
				label={<IconLabel icon={icons.id} label={__('Value', 'eightshift-forms')} />}
				value={submitValue}
				onChange={(value) => setAttributes({ [getAttrKey('submitValue', attributes, manifest)]: value })}
			/>

			<ToggleControl
				label={__('Show advanced options', 'eightshift-forms')}
				checked={showAdvanced}
				onChange={() => setShowAdvanced(!showAdvanced)}
			/>

			{showAdvanced &&
				<>
					<TextControl
						label={<IconLabel icon={icons.id} label={__('Tracking Code', 'eightshift-forms')} />}
						value={submitTracking}
						onChange={(value) => setAttributes({ [getAttrKey('submitTracking', attributes, manifest)]: value })}
					/>

					<TextControl
						label={<IconLabel icon={icons.id} label={__('Id', 'eightshift-forms')} />}
						value={submitId}
						onChange={(value) => setAttributes({ [getAttrKey('submitId', attributes, manifest)]: value })}
					/>

					<SelectControl
						label={<IconLabel icon={icons.id} label={__('Type', 'eightshift-forms')} />}
						value={submitType}
						options={getOption('submitType', attributes, manifest)}
						onChange={(value) => setAttributes({ [getAttrKey('submitType', attributes, manifest)]: value })}
					/>

					<IconToggle
						icon={icons.play}
						label={__('Is Disabled', 'eightshift-forms')}
						checked={submitIsDisabled}
						onChange={(value) => setAttributes({ [getAttrKey('submitIsDisabled', attributes, manifest)]: value })}
					/>
				</>
			}
		</>
	);
};
