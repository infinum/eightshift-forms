import React from 'react';
import { __ } from '@wordpress/i18n';
import { TextControl, ToggleControl } from '@wordpress/components';
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
	const formActionExternal = checkAttr('formActionExternal', attributes, manifest);
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
				help={__('Custom form action that will process form data.' ,'eightshift-forms')}
				onChange={(value) => setAttributes({ [getAttrKey('formAction', attributes, manifest)]: value })}
			/>

			<ToggleControl
				label={__('Process this form externally?', 'eightshift-forms')}
				checked={formActionExternal}
				help={__('Select this option to redirect and process the form data on external site. On successful form submission the user will be redirected to an external site.' ,'eightshift-forms')}
				onChange={(value) => setAttributes({ [getAttrKey('formActionExternal', attributes, manifest)]: value })}
			/>

			<TextControl
				label={<IconLabel icon={icons.id} label={__('Unique identifier', 'eightshift-forms')} />}
				value={formId}
				onChange={(value) => setAttributes({ [getAttrKey('formId', attributes, manifest)]: value })}
			/>
		</>
	);
};
