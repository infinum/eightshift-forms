import React from 'react';
import { __ } from '@wordpress/i18n';
import { TextControl, PanelBody } from '@wordpress/components';
import { icons, checkAttr, getAttrKey, IconLabel, props, Section, IconToggle } from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../../components/field/components/field-options';
import { FieldOptionsAdvanced } from '../../field/components/field-options-advanced';
import { isOptionDisabled, NameFieldLabel } from './../../utils';
import manifest from '../manifest.json';

export const StepOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const stepName = checkAttr('stepName', attributes, manifest);

	return (
		<>
			<PanelBody title={__('Step', 'eightshift-forms')}>
				<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
					<TextControl
						label={<NameFieldLabel value={stepName} />}
						help={__('Identifies the step within form multi step flow. Must be unique.', 'eightshift-forms')}
						value={stepName}
						onChange={(value) => setAttributes({ [getAttrKey('stepName', attributes, manifest)]: value })}
					/>
				</Section>
			</PanelBody>
		</>
	);
};
