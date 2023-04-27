import React from 'react';
import { __ } from '@wordpress/i18n';
import { TextControl, PanelBody } from '@wordpress/components';
import { checkAttr, getAttrKey, props, icons, Section, IconToggle } from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../field/components/field-options';
import { FieldOptionsAdvanced } from '../../field/components/field-options-advanced';
import manifest from '../manifest.json';
import { isOptionDisabled, NameFieldLabel } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';

export const RadiosOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const radiosName = checkAttr('radiosName', attributes, manifest);
	const radiosIsRequired = checkAttr('radiosIsRequired', attributes, manifest);
	const radiosDisabledOptions = checkAttr('radiosDisabledOptions', attributes, manifest);

	return (
		<>
			<PanelBody title={__('Radio buttons', 'eightshift-forms')}>
				<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
					<TextControl
						label={<NameFieldLabel value={radiosName} />}
						help={__('Identifies the field within form submission data. Must be unique.', 'eightshift-forms')}
						value={radiosName}
						onChange={(value) => setAttributes({ [getAttrKey('radiosName', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('radiosName', attributes, manifest), radiosDisabledOptions)}
						className='es-no-field-spacing'
					/>
				</Section>

				<FieldOptions
					{...props('field', attributes, {
						fieldDisabledOptions: radiosDisabledOptions,
					})}
					additionalControls={<FieldOptionsAdvanced {...props('field', attributes)} />}
				/>

				<Section icon={icons.checks} label={__('Validation', 'eightshift-forms')} >
					<IconToggle
						icon={icons.required}
						label={__('Required', 'eightshift-forms')}
						checked={radiosIsRequired}
						onChange={(value) => setAttributes({ [getAttrKey('radiosIsRequired', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('radiosIsRequired', attributes, manifest), radiosDisabledOptions)}
						noBottomSpacing
					/>
				</Section>
			</PanelBody>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsParentName: radiosName,
				})}
			/>
		</>
	);
};
