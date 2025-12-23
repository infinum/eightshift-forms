import React from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { icons } from '@eightshift/ui-components/icons';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs-tailwind/scripts';
import { NameField } from './../../utils';
import { RichLabel, ContainerPanel, InputField, Spacer } from '@eightshift/ui-components';
import manifest from '../manifest.json';

export const StepOptions = (attributes) => {
	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const stepName = checkAttr('stepName', attributes, manifest);
	const stepLabel = checkAttr('stepLabel', attributes, manifest);
	const stepPrevLabel = checkAttr('stepPrevLabel', attributes, manifest);
	const stepNextLabel = checkAttr('stepNextLabel', attributes, manifest);

	return (
		<ContainerPanel>
			<Spacer
				border
				icon={icons.options}
				text={__('General', 'eightshift-forms')}
			/>
			<NameField
				value={stepName}
				help={__('Used to identify the step within form multi step flow.', 'eightshift-forms')}
				attribute={getAttrKey('stepName', attributes, manifest)}
				setAttributes={setAttributes}
				type={'step'}
				isChanged={isNameChanged}
				setIsChanged={setIsNameChanged}
			/>

			<InputField
				help={__(
					'This label will not be shown on the frontend, this is only for easier configuration.',
					'eightshift-forms',
				)}
				value={stepLabel}
				onChange={(value) => setAttributes({ [getAttrKey('stepLabel', attributes, manifest)]: value })}
			/>

			<Spacer
				border
				icon={icons.buttonFilled}
				text={__('Buttons', 'eightshift-forms')}
			/>

			<InputField
				label={
					<RichLabel
						icon={icons.tag}
						label={__('Previous button label', 'eightshift-forms')}
					/>
				}
				placeholder={__('Previous', 'eightshift-forms')}
				value={stepPrevLabel}
				onChange={(value) => setAttributes({ [getAttrKey('stepPrevLabel', attributes, manifest)]: value })}
			/>

			<InputField
				label={
					<RichLabel
						icon={icons.tag}
						label={__('Next button label', 'eightshift-forms')}
					/>
				}
				placeholder={__('Next', 'eightshift-forms')}
				value={stepNextLabel}
				onChange={(value) => setAttributes({ [getAttrKey('stepNextLabel', attributes, manifest)]: value })}
			/>
		</ContainerPanel>
	);
};
