import React from 'react';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { InputField, Toggle, Spacer } from '@eightshift/ui-components';
import { icons } from '@eightshift/ui-components/icons';
import manifest from '../manifest.json';

export const FormOptions = (attributes) => {
	const { setAttributes } = attributes;

	const formName = checkAttr('formName', attributes, manifest);
	const formAction = checkAttr('formAction', attributes, manifest);
	const formActionExternal = checkAttr('formActionExternal', attributes, manifest);
	const formId = checkAttr('formId', attributes, manifest);

	return (
		<>
			<Spacer
				border
				icon={icons.options}
				text={__('General', 'eightshift-forms')}
			/>
			<InputField
				icon={icons.tag}
				label={__('Form name', 'eightshift-forms')}
				help={__(
					'Used as a name attribute for form element Useful if you want to add additional code style for the form.',
					'eightshift-forms',
				)}
				value={formName}
				onChange={(value) => setAttributes({ [getAttrKey('formName', attributes, manifest)]: value })}
			/>

			<Spacer
				border
				icon={icons.tools}
				text={__('Advanced', 'eightshift-forms')}
			/>

			<InputField
				icon={icons.gears}
				label={__('Custom action', 'eightshift-forms')}
				value={formAction}
				help={__('Custom form action that will process form data.', 'eightshift-forms')}
				onChange={(value) => setAttributes({ [getAttrKey('formAction', attributes, manifest)]: value })}
			/>

			<Toggle
				icon={icons.externalLink}
				label={__('Process form externally', 'eightshift-forms')}
				checked={formActionExternal}
				help={__(
					'If enabled, after a successful submission the user will be redirected to the external site, which should be set up to process the form entry.',
					'eightshift-forms',
				)}
				onChange={(value) => setAttributes({ [getAttrKey('formActionExternal', attributes, manifest)]: value })}
			/>

			<InputField
				icon={icons.id}
				label={__('Unique identifier', 'eightshift-forms')}
				value={formId}
				onChange={(value) => setAttributes({ [getAttrKey('formId', attributes, manifest)]: value })}
			/>
		</>
	);
};
