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

	return (
		<>
			<TextControl
				label={<IconLabel icon={icons.id} label={__('Label', 'eightshift-forms')} />}
				value={fieldLabel}
				onChange={(value) => setAttributes({ [getAttrKey('fieldLabel', attributes, manifest)]: value })}
			/>
		</>
	);
};
