import React from 'react';
import { __ } from '@wordpress/i18n';
import { TextControl, PanelBody, Button } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	props,
	FancyDivider,
} from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../field/components/field-options';
import { FieldOptionsAdvanced } from '../../field/components/field-options-advanced';
import manifest from '../manifest.json';
import { isOptionDisabled, MissingName } from '../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';

export const CountryOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const countryName = checkAttr('countryName', attributes, manifest);
	const countryIsDisabled = checkAttr('countryIsDisabled', attributes, manifest);
	const countryIsRequired = checkAttr('countryIsRequired', attributes, manifest);
	const countryTracking = checkAttr('countryTracking', attributes, manifest);
	const countryDisabledOptions = checkAttr('countryDisabledOptions', attributes, manifest);
	const countryUseSearch = checkAttr('countryUseSearch', attributes, manifest);

	return (
		<>
			<PanelBody title={__('Country', 'eightshift-forms')}>
				<FieldOptions
					{...props('field', attributes, {
						fieldDisabledOptions: countryDisabledOptions,
					})}
				/>

				<FancyDivider label={__('Validation', 'eightshift-forms')} />

				<div className='es-h-spaced'>
					<Button
						icon={icons.fieldRequired}
						isPressed={countryIsRequired}
						onClick={() => setAttributes({ [getAttrKey('countryIsRequired', attributes, manifest)]: !countryIsRequired })}
						disabled={isOptionDisabled(getAttrKey('countryIsRequired', attributes, manifest), countryDisabledOptions)}
					>
						{__('Required', 'eightshift-forms')}
					</Button>
				</div>

				<FancyDivider label={__('Advanced', 'eightshift-forms')} />

				<TextControl
					label={<IconLabel icon={icons.fieldName} label={__('Name', 'eightshift-forms')} />}
					help={__('Should be unique! Used to identify the field within form submission data.', 'eightshift-forms')}
					value={countryName}
					onChange={(value) => setAttributes({ [getAttrKey('countryName', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('countryName', attributes, manifest), countryDisabledOptions)}
				/>
				<MissingName value={countryName} />

				<div className='es-h-spaced'>
					<Button
						icon={icons.fieldDisabled}
						isPressed={countryIsDisabled}
						onClick={() => setAttributes({ [getAttrKey('countryIsDisabled', attributes, manifest)]: !countryIsDisabled })}
						disabled={isOptionDisabled(getAttrKey('countryIsDisabled', attributes, manifest), countryDisabledOptions)}
					>
						{__('Disabled', 'eightshift-forms')}
					</Button>
				</div>

				<div className='es-h-spaced'>
					<Button
						icon={icons.fieldRequired}
						isPressed={countryUseSearch}
						onClick={() => setAttributes({ [getAttrKey('countryUseSearch', attributes, manifest)]: !countryUseSearch })}
						disabled={isOptionDisabled(getAttrKey('countryUseSearch', attributes, manifest), countryDisabledOptions)}
					>
						{__('Allow search', 'eightshift-forms')}
					</Button>
				</div>

				<FancyDivider label={__('Tracking', 'eightshift-forms')} />

				<TextControl
					label={<IconLabel icon={icons.code} label={__('GTM tracking code', 'eightshift-forms')} />}
					value={countryTracking}
					onChange={(value) => setAttributes({ [getAttrKey('countryTracking', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('countryTracking', attributes, manifest), countryDisabledOptions)}
				/>
			</PanelBody>

			<FieldOptionsAdvanced
				{...props('field', attributes)}
			/>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsParentName: countryName,
				})}
			/>
		</>
	);
};
