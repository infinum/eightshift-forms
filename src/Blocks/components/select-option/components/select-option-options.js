import React from 'react';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { TextControl, ToggleControl } from '@wordpress/components';
import { checkAttr, getAttrKey, icons } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const SelectOptionOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const selectOptionLabel = checkAttr('selectOptionLabel', attributes, manifest);
	const selectOptionValue = checkAttr('selectOptionValue', attributes, manifest);
	const selectOptionIsSelected = checkAttr('selectOptionIsSelected', attributes, manifest);
	const selectOptionIsDisabled = checkAttr('selectOptionIsDisabled', attributes, manifest);

	const [showAdvanced, setShowAdvanced] = useState(false);

	return (
		<>
			<TextControl
				value={selectOptionLabel}
				onChange={(value) => setAttributes({ [getAttrKey('selectOptionLabel', attributes, manifest)]: value })}
				label={__('Label', 'eightshift-forms')}
			/>

			<ToggleControl
				checked={showAdvanced}
				onChange={() => setShowAdvanced(!showAdvanced)}
			/>

			{showAdvanced &&
				<>
					<TextControl
						label={__('Value', 'eightshift-forms')}
						value={selectOptionValue}
						onChange={(value) => setAttributes({ [getAttrKey('selectOptionValue', attributes, manifest)]: value })}
					/>

					<ToggleControl
						icon={icons.play}
						label={__('Is Selected', 'eightshift-forms')}
						checked={selectOptionIsSelected}
						onChange={(value) => setAttributes({ [getAttrKey('selectOptionIsSelected', attributes, manifest)]: value })}
					/>

					<ToggleControl
						icon={icons.play}
						label={__('Is Disabled', 'eightshift-forms')}
						checked={selectOptionIsDisabled}
						onChange={(value) => setAttributes({ [getAttrKey('selectOptionIsDisabled', attributes, manifest)]: value })}
					/>
				</>
			}
		</>
	);
};
