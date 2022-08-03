import React from 'react';
import { __ } from '@wordpress/i18n';
import { TextControl } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	FancyDivider
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FormOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const formName = checkAttr('formName', attributes, manifest);
	const formAction = checkAttr('formAction', attributes, manifest);
	const formId = checkAttr('formId', attributes, manifest);

	return (
		<>
			<TextControl
				label={<IconLabel icon={icons.fieldName} label={__('Form name', 'eightshift-forms')} />}
				help={__('Used to identify and reference the form. If not set, a random name will be generated.', 'eightshift-forms')}
				value={formName}
				onChange={(value) => setAttributes({ [getAttrKey('formName', attributes, manifest)]: value })}
			/>

			<FancyDivider label={__('Advanced', 'eightshift-forms')} />

			<TextControl
				label={<IconLabel icon={icons.fieldName} label={__('Form Action', 'eightshift-forms')} />}
				value={formAction}
				help={__('Custom form action that will process form data. Setting this value will redirect user to the provided URL on succesfull form submit.' ,'eightshift-forms')}
				onChange={(value) => setAttributes({ [getAttrKey('formAction', attributes, manifest)]: value })}
			/>

			<TextControl
				label={<IconLabel icon={icons.id} label={__('Unique identifier', 'eightshift-forms')} />}
				value={formId}
				onChange={(value) => setAttributes({ [getAttrKey('formId', attributes, manifest)]: value })}
			/>
		</>
	);
};
