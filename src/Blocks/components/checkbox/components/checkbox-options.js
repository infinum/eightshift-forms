import React from 'react';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { TextControl } from '@wordpress/components';
import { checkAttr, getAttrKey, icons, ComponentUseToggle, IconToggle } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const CheckboxOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const checkboxLabel = checkAttr('checkboxLabel', attributes, manifest);
	const checkboxName = checkAttr('checkboxName', attributes, manifest);
	const checkboxIsChecked = checkAttr('checkboxIsChecked', attributes, manifest);
	const checkboxIsDisabled = checkAttr('checkboxIsDisabled', attributes, manifest);
	const checkboxIsReadOnly = checkAttr('checkboxIsReadOnly', attributes, manifest);
	const checkboxIsRequired = checkAttr('checkboxIsRequired', attributes, manifest);
	const checkboxTracking = checkAttr('checkboxTracking', attributes, manifest);

	const [showAdvanced, setShowAdvanced] = useState(false);
	const [showValidation, setShowValidation] = useState(false);

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
						label={__('Name', 'eightshift-forms')}
						help={__('Set unique field name. If not set field will have an generic name.', 'eightshift-forms')}
						value={checkboxName}
						onChange={(value) => setAttributes({ [getAttrKey('checkboxName', attributes, manifest)]: value })}
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

			<ComponentUseToggle
				label={__('Show validation options', 'eightshift-forms')}
				checked={showValidation}
				onChange={() => setShowValidation(!showValidation)}
				showUseToggle={true}
				showLabel={true}
			/>

			{showValidation &&
				<>
					<IconToggle
						icon={icons.play}
						label={__('Is Required', 'eightshift-forms')}
						checked={checkboxIsRequired}
						onChange={(value) => setAttributes({ [getAttrKey('checkboxIsRequired', attributes, manifest)]: value })}
					/>
				</>
			}
		</>
	);
};
