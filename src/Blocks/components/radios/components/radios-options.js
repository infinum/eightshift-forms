import React from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { TextControl, PanelBody } from '@wordpress/components';
import { checkAttr, getAttrKey, props, icons, Section, IconToggle, IconLabel } from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../field/components/field-options';
import { FieldOptionsAdvanced } from '../../field/components/field-options-advanced';
import manifest from '../manifest.json';
import { isOptionDisabled, NameFieldLabel, NameChangeWarning } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';

export const RadiosOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const radiosName = checkAttr('radiosName', attributes, manifest);
	const radiosIsRequired = checkAttr('radiosIsRequired', attributes, manifest);
	const radiosDisabledOptions = checkAttr('radiosDisabledOptions', attributes, manifest);
	const radiosTracking = checkAttr('radiosTracking', attributes, manifest);

	return (
		<>
			<PanelBody title={__('Radio buttons', 'eightshift-forms')}>
				<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
					<TextControl
						label={<NameFieldLabel value={radiosName} />}
						help={__('Identifies the field within form submission data. Must be unique.', 'eightshift-forms')}
						value={radiosName}
						onChange={(value) => {
							setIsNameChanged(true);
							setAttributes({ [getAttrKey('radiosName', attributes, manifest)]: value });
						}}
						disabled={isOptionDisabled(getAttrKey('radiosName', attributes, manifest), radiosDisabledOptions)}
						className='es-no-field-spacing'
					/>
					<NameChangeWarning isChanged={isNameChanged} />
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
				<Section icon={icons.alignHorizontalVertical} label={__('Tracking', 'eightshift-forms')} noBottomSpacing>
					<TextControl
						label={<IconLabel icon={icons.googleTagManager} label={__('GTM tracking code', 'eightshift-forms')} />}
						value={radiosTracking}
						onChange={(value) => setAttributes({ [getAttrKey('radiosTracking', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('radiosTracking', attributes, manifest), radiosDisabledOptions)}
						className='es-no-field-spacing'
					/>
				</Section>
			</PanelBody>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: radiosName,
				})}
			/>
		</>
	);
};
