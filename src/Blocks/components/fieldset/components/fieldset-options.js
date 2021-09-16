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

export const FieldsetOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const fieldsetLegend = checkAttr('fieldsetLegend', attributes, manifest);
	const fieldsetId = checkAttr('fieldsetId', attributes, manifest);

	return (
		<>
			<TextControl
				label={<IconLabel icon={icons.id} label={__('Legend', 'eightshift-forms')} />}
				value={fieldsetLegend}
				onChange={(value) => setAttributes({ [getAttrKey('fieldsetLegend', attributes, manifest)]: value })}
			/>

			<TextControl
				label={<IconLabel icon={icons.id} label={__('Id', 'eightshift-forms')} />}
				value={fieldsetId}
				onChange={(value) => setAttributes({ [getAttrKey('fieldsetId', attributes, manifest)]: value })}
			/>
		</>
	);
};
