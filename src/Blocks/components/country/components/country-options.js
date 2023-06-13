import React from 'react';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { TextControl, PanelBody } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	props,
	Section,
	IconToggle,
} from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../field/components/field-options';
import { FieldOptionsAdvanced } from '../../field/components/field-options-advanced';
import manifest from '../manifest.json';
import { isOptionDisabled, NameFieldLabel, NameChangeWarning } from '../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';

export const CountryOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const countryName = checkAttr('countryName', attributes, manifest);
	const countryIsDisabled = checkAttr('countryIsDisabled', attributes, manifest);
	const countryIsRequired = checkAttr('countryIsRequired', attributes, manifest);
	const countryTracking = checkAttr('countryTracking', attributes, manifest);
	const countryDisabledOptions = checkAttr('countryDisabledOptions', attributes, manifest);
	const countryUseSearch = checkAttr('countryUseSearch', attributes, manifest);

	return (
		<>
			<PanelBody title={__('Country', 'eightshift-forms')}>
				<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
					<TextControl
						label={<NameFieldLabel value={countryName} />}
						help={__('Identifies the field within form submission data. Must be unique.', 'eightshift-forms')}
						value={countryName}
						onChange={(value) => {
							setIsNameChanged(true);
							setAttributes({ [getAttrKey('countryName', attributes, manifest)]: value });
						}}
						disabled={isOptionDisabled(getAttrKey('countryName', attributes, manifest), countryDisabledOptions)}
					/>
					<NameChangeWarning isChanged={isNameChanged} />
				</Section>

				<FieldOptions
					{...props('field', attributes, {
						fieldDisabledOptions: countryDisabledOptions,
					})}
					additionalControls={<FieldOptionsAdvanced {...props('field', attributes)} />}
				/>

				<Section icon={icons.checks} label={__('Validation', 'eightshift-forms')}>
					<IconToggle
						icon={icons.required}
						label={__('Required', 'eightshift-forms')}
						checked={countryIsRequired}
						onChange={(value) => setAttributes({ [getAttrKey('countryIsRequired', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('countryIsRequired', attributes, manifest), countryDisabledOptions)}
						noBottomSpacing
					/>
				</Section>

				<Section icon={icons.tools} label={__('Advanced', 'eightshift-forms')}>
					<IconToggle
						icon={icons.cursorDisabled}
						label={__('Disabled', 'eightshift-forms')}
						checked={countryIsDisabled}
						onChange={(value) => setAttributes({ [getAttrKey('countryIsDisabled', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('countryIsDisabled', attributes, manifest), countryDisabledOptions)}
					/>

					<IconToggle
						icon={icons.search}
						label={__('Search', 'eightshift-forms')}
						checked={countryUseSearch}
						onChange={(value) => setAttributes({ [getAttrKey('countryUseSearch', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('countryUseSearch', attributes, manifest), countryDisabledOptions)}
						noBottomSpacing
					/>
				</Section>

				<Section icon={icons.alignHorizontalVertical} label={__('Tracking', 'eightshift-forms')} noBottomSpacing>
					<TextControl
						label={<IconLabel icon={icons.googleTagManager} label={__('GTM tracking code', 'eightshift-forms')} />}
						value={countryTracking}
						onChange={(value) => setAttributes({ [getAttrKey('countryTracking', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('countryTracking', attributes, manifest), countryDisabledOptions)}
						className='es-no-field-spacing'
					/>
				</Section>
			</PanelBody>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: countryName,
				})}
			/>
		</>
	);
};
