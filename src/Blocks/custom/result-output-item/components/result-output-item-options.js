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
		<PanelBody title={__('Result Item', 'infobip')}>
			<TextControl
				label={<IconLabel icon={icons.id} label={__('Variable Name', 'infobip')} />}
				value={resultOutputItemName}
				onChange={(value) => setAttributes({ [getAttrKey('resultOutputItemName', attributes, manifest)]: value })}
			/>
			<TextControl
				label={<IconLabel icon={icons.id} label={__('Variable Value', 'infobip')} />}
				value={resultOutputItemValue}
				onChange={(value) => setAttributes({ [getAttrKey('resultOutputItemValue', attributes, manifest)]: value })}
			/>

		<Notification
			text={__('This block requires some code implementation. When form is submitted you must provide a filter output based on the field settings or use our premium Computed fields addon.', 'eightshift-forms')}
			type={'warning'}
		/>

		</PanelBody>
	);
};