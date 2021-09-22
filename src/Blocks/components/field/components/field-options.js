import React from 'react';
import { __ } from '@wordpress/i18n';
import { TextControl} from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FieldOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const fieldLabel = checkAttr('fieldLabel', attributes, manifest);
	const fieldHelp = checkAttr('fieldHelp', attributes, manifest);

	return (
		<>
			<TextControl
				label={<IconLabel icon={icons.id} label={__('Label', 'eightshift-forms')} />}
				help={__('Set label for grouping multiple checkboxes/radios in one field box.', 'eightshift-forms')}
				value={fieldLabel}
				onChange={(value) => setAttributes({ [getAttrKey('fieldLabel', attributes, manifest)]: value })}
			/>

			<TextControl
				label={<IconLabel icon={icons.id} label={__('Help', 'eightshift-forms')} />}
				help={__('Set field help info text.', 'eightshift-forms')}
				value={fieldHelp}
				onChange={(value) => setAttributes({ [getAttrKey('fieldHelp', attributes, manifest)]: value })}
			/>
		</>
	);
};
