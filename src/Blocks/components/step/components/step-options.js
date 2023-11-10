import React from 'react';
import { select } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { TextControl, PanelBody } from '@wordpress/components';
import { icons, checkAttr, getAttrKey, IconLabel, Section, STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { NameField } from './../../utils';

export const StepOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('step');

	const {
		setAttributes,
	} = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const stepName = checkAttr('stepName', attributes, manifest);
	const stepLabel = checkAttr('stepLabel', attributes, manifest);
	const stepPrevLabel = checkAttr('stepPrevLabel', attributes, manifest);
	const stepNextLabel = checkAttr('stepNextLabel', attributes, manifest);

	return (
		<>
			<PanelBody title={__('Step', 'eightshift-forms')}>
				<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
					<NameField
						value={stepName}
						help={__('Used to identify the step within form multi step flow.', 'eightshift-forms')}
						attribute={getAttrKey('stepName', attributes, manifest)}
						setAttributes={setAttributes}
						type={'step'}
						isChanged={isNameChanged}
						setIsChanged={setIsNameChanged}
					/>
				</Section>

				<Section icon={icons.tag} label={__('Label', 'eightshift-forms')}>
					<TextControl
						help={__('This label will not be shown on the frontend, this is only for easier configuration.', 'eightshift-forms')}
						value={stepLabel}
						onChange={(value) => setAttributes({ [getAttrKey('stepLabel', attributes, manifest)]: value })}
					/>
				</Section>

				<Section icon={icons.buttonFilled} label={__('Buttons', 'eightshift-forms')}>

					<TextControl
						label={<IconLabel icon={icons.tag} label={__('Previous button label', 'eightshift-forms')} />}
						placeholder={__('Previous', 'eightshift-forms')}
						value={stepPrevLabel}
						onChange={(value) => setAttributes({ [getAttrKey('stepPrevLabel', attributes, manifest)]: value })}
					/>

					<TextControl
						label={<IconLabel icon={icons.tag} label={__('Next button label', 'eightshift-forms')} />}
						placeholder={__('Next', 'eightshift-forms')}
						value={stepNextLabel}
						onChange={(value) => setAttributes({ [getAttrKey('stepNextLabel', attributes, manifest)]: value })}
					/>
				</Section>
			</PanelBody>
		</>
	);
};
