import React from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { TextControl, PanelBody } from '@wordpress/components';
import { icons, checkAttr, getAttrKey, IconLabel, Section } from '@eightshift/frontend-libs/scripts';
import { NameFieldLabel, NameChangeWarning } from './../../utils';
import manifest from '../manifest.json';

export const StepOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const stepName = checkAttr('stepName', attributes, manifest);
	const stepLabel = checkAttr('stepLabel', attributes, manifest);

	return (
		<>
			<PanelBody title={__('Step', 'eightshift-forms')}>
				<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
					<TextControl
						label={<NameFieldLabel value={stepName} />}
						help={__('Identifies the step within form multi step flow. Must be unique.', 'eightshift-forms')}
						value={stepName}
						onChange={(value) => {
							setIsNameChanged(true);
							setAttributes({ [getAttrKey('stepName', attributes, manifest)]: value });
						}}
					/>

					<NameChangeWarning isChanged={isNameChanged} type={'step'} />

					<TextControl
						label={<IconLabel icon={icons.tag} label={__('Label', 'eightshift-forms')} />}
						help={__('This label will not be shown on the frontend, this is only for easier configuration.', 'eightshift-forms')}
						value={stepLabel}
						onChange={(value) => setAttributes({ [getAttrKey('stepLabel', attributes, manifest)]: value })}
					/>
				</Section>
			</PanelBody>
		</>
	);
};
