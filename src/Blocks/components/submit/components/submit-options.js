import React from 'react';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { TextControl } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	IconToggle,
	ComponentUseToggle
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const SubmitOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const submitValue = checkAttr('submitValue', attributes, manifest);
	const submitIsDisabled = checkAttr('submitIsDisabled', attributes, manifest);
	const submitTracking = checkAttr('submitTracking', attributes, manifest);

	const [showAdvanced, setShowAdvanced] = useState(false);

	return (
		<>
			<TextControl
				label={<IconLabel icon={icons.fieldValue} label={__('Value', 'eightshift-forms')} />}
				help={__('Provide button text.', 'eightshift-forms')}
				value={submitValue}
				onChange={(value) => setAttributes({ [getAttrKey('submitValue', attributes, manifest)]: value })}
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
						label={<IconLabel icon={icons.code} label={__('Tracking Code', 'eightshift-forms')} />}
						help={__('Provide GTM tracking code.', 'eightshift-forms')}
						value={submitTracking}
						onChange={(value) => setAttributes({ [getAttrKey('submitTracking', attributes, manifest)]: value })}
					/>

					<IconToggle
						icon={icons.fieldDisabled}
						label={__('Is Disabled', 'eightshift-forms')}
						checked={submitIsDisabled}
						onChange={(value) => setAttributes({ [getAttrKey('submitIsDisabled', attributes, manifest)]: value })}
					/>
				</>
			}
		</>
	);
};
