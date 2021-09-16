import React from 'react';
import { TextControl, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const SelectOptionOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const selectOptionLabel = checkAttr('selectOptionLabel', attributes, manifest);
	const selectOptionValue = checkAttr('selectOptionValue', attributes, manifest);
	const selectOptionIsSelected = checkAttr('selectOptionIsSelected', attributes, manifest);
	const selectOptionIsDisabled = checkAttr('selectOptionIsDisabled', attributes, manifest);

	return (
		<>
			<TextControl
				value={selectOptionLabel}
				onChange={(value) => setAttributes({ [getAttrKey('selectOptionLabel', attributes, manifest)]: value })}
				placeholder={__('Option Label', 'eightshift-forms')}
			/>

			<TextControl
				value={selectOptionValue}
				onChange={(value) => setAttributes({ [getAttrKey('selectOptionValue', attributes, manifest)]: value })}
				placeholder={__('Option Value', 'eightshift-forms')}
			/>

			<ToggleControl
				label={__('Is Selected', 'eightshift-forms')}
				checked={selectOptionIsSelected}
				onChange={(value) => setAttributes({ [getAttrKey('selectOptionIsSelected', attributes, manifest)]: value })}
			/>

			<ToggleControl
				label={__('Is Disabled', 'eightshift-forms')}
				checked={selectOptionIsDisabled}
				onChange={(value) => setAttributes({ [getAttrKey('selectOptionIsDisabled', attributes, manifest)]: value })}
			/>
		</>
	);
};
