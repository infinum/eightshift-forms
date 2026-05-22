import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { InputField, Toggle, Container, HStack } from '@eightshift/ui-components';
import { externalLink, gears, id, tag } from '@eightshift/ui-components/icons';
import manifest from '../manifest.json';
import { HelpTooltip } from '../../../assets/scripts/help-tooltip';

export const FormOptions = (attributes) => {
	const { setAttributes } = attributes;

	const formName = checkAttr('formName', attributes, manifest);

	return (
		<Container standalone>
			<InputField
				icon={tag}
				label={
					<HStack>
						{__('Name', 'eightshift-forms')}

						<HelpTooltip>
							{__('Used as a name attribute for form element.', 'eightshift-forms')}

							<br />
							<br />

							{__('Useful if you want to add additional code style for the form.', 'eightshift-forms')}
						</HelpTooltip>
					</HStack>
				}
				value={formName}
				onChange={(value) => setAttributes({ [getAttrKey('formName', attributes, manifest)]: value })}
				monospaceFont
				inline
			/>
		</Container>
	);
};

export const FormOptionsAdvanced = (attributes) => {
	const { setAttributes } = attributes;

	const formAction = checkAttr('formAction', attributes, manifest);
	const formActionExternal = checkAttr('formActionExternal', attributes, manifest);
	const formId = checkAttr('formId', attributes, manifest);

	return (
		<>
			<Container standalone>
				<InputField
					icon={gears}
					label={__('Custom action', 'eightshift-forms')}
					value={formAction}
					onChange={(value) => setAttributes({ [getAttrKey('formAction', attributes, manifest)]: value })}
					actions={
						<HelpTooltip>{__('Custom form action that will process form data.', 'eightshift-forms')}</HelpTooltip>
					}
					monospaceFont
				/>
			</Container>

			<Container standalone>
				<Toggle
					icon={externalLink}
					label={
						<HStack>
							{__('Process form externally', 'eightshift-forms')}

							<HelpTooltip>
								{__(
									'If enabled, after a successful submission the user will be redirected to the external site, which should be set up to process the form entry.',
									'eightshift-forms',
								)}
							</HelpTooltip>
						</HStack>
					}
					checked={formActionExternal}
					onChange={(value) => setAttributes({ [getAttrKey('formActionExternal', attributes, manifest)]: value })}
				/>
			</Container>

			<Container standalone>
				<InputField
					icon={id}
					label={__('Unique identifier', 'eightshift-forms')}
					value={formId}
					onChange={(value) => setAttributes({ [getAttrKey('formId', attributes, manifest)]: value })}
					monospaceFont
				/>
			</Container>
		</>
	);
};
