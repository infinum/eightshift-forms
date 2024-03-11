import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl } from '@wordpress/components';
import { checkAttr, getAttrKey, icons, IconLabel, Notification } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const ResultOutputItemOptions = ({
	attributes,
	setAttributes,
}) => {

	const resultOutputItemName = checkAttr('resultOutputItemName', attributes, manifest);
	const resultOutputItemValue = checkAttr('resultOutputItemValue', attributes, manifest);

	return (
		<PanelBody title={__('Result Item', 'eightshift-forms')}>
			<TextControl
				label={<IconLabel icon={icons.id} label={__('Variable Name', 'eightshift-forms')} />}
				value={resultOutputItemName}
				onChange={(value) => setAttributes({ [getAttrKey('resultOutputItemName', attributes, manifest)]: value })}
			/>
			<TextControl
				label={<IconLabel icon={icons.id} label={__('Variable Value', 'eightshift-forms')} />}
				value={resultOutputItemValue}
				onChange={(value) => setAttributes({ [getAttrKey('resultOutputItemValue', attributes, manifest)]: value })}
			/>

		<Notification
			text={__('The block will not show anything if filters are not added through code! If you have the Computed fields add-on, its output is also supported.', 'eightshift-forms')}
			type='warning'
		/>

		</PanelBody>
	);
};
