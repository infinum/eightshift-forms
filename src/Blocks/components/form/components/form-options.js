import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { checkAttr, getAttrKey, Section, Toggle, STORE_NAME } from '@eightshift/frontend-libs-tailwind/scripts';
import { icons } from '@eightshift/ui-components/icons';
import { InputField } from '@eightshift/ui-components';

export const FormOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('form');

	const { setAttributes } = attributes;

	const formName = checkAttr('formName', attributes, manifest);
	const formAction = checkAttr('formAction', attributes, manifest);
	const formActionExternal = checkAttr('formActionExternal', attributes, manifest);
	const formId = checkAttr('formId', attributes, manifest);

	return (
		<>
			<Section
				icon={icons.options}
				label={__('General', 'eightshift-forms')}
			>
				<InputField
					icon={icons.tag}
					label={__('Form name', 'eightshift-forms')}
					help={__('Used as a name attribute for form element Useful if you want to add additional code style for the form.', 'eightshift-forms')}
					value={formName}
					onChange={(value) => setAttributes({ [getAttrKey('formName', attributes, manifest)]: value })}
				/>
			</Section>

			<Section
				icon={icons.tools}
				label={__('Advanced', 'eightshift-forms')}
				collapsable
			>
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
			</Section>
		</>
	);
};
