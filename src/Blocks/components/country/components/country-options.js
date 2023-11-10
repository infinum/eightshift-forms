import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
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
	STORE_NAME,
} from '@eightshift/frontend-libs/scripts';
import { FieldOptions, FieldOptionsMore, FieldOptionsLayout, FieldOptionsVisibility } from '../../field/components/field-options';
import { isOptionDisabled, NameField } from '../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';

export const CountryOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('country');

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
	const countryPlaceholder = checkAttr('countryPlaceholder', attributes, manifest);
	const countryUseLabelAsPlaceholder = checkAttr('countryUseLabelAsPlaceholder', attributes, manifest);

	return (
		<>
			<PanelBody title={__('Country', 'eightshift-forms')}>
				<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
					<NameField
						value={countryName}
						attribute={getAttrKey('countryName', attributes, manifest)}
						disabledOptions={countryDisabledOptions}
						setAttributes={setAttributes}
						type={'country'}
						isChanged={isNameChanged}
						setIsChanged={setIsNameChanged}
					/>
				</Section>

				<FieldOptions
					{...props('field', attributes, {
						fieldDisabledOptions: countryDisabledOptions,
					})}
				/>

				<Section icon={icons.fieldPlaceholder} label={__('Placeholder', 'eightshift-forms')}>
					{!countryUseLabelAsPlaceholder &&
						<TextControl
							help={__('Shown when the field is empty', 'eightshift-forms')}
							value={countryPlaceholder}
							onChange={(value) => setAttributes({ [getAttrKey('countryPlaceholder', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('countryPlaceholder', attributes, manifest), countryDisabledOptions)}
							className='es-no-field-spacing'
						/>
					}
					<IconToggle
						icon={icons.fieldPlaceholder}
						label={__('Use label as placeholder', 'eightshift-forms')}
						checked={countryUseLabelAsPlaceholder}
						onChange={(value) => {
							setAttributes({ [getAttrKey('countryPlaceholder', attributes, manifest)]: undefined });
							setAttributes({ [getAttrKey('countryUseLabelAsPlaceholder', attributes, manifest)]: value });
						}}
					/>
				</Section>

				<FieldOptionsLayout
					{...props('field', attributes, {
						fieldDisabledOptions: countryDisabledOptions,
					})}
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
					<FieldOptionsVisibility
						{...props('field', attributes, {
							fieldDisabledOptions: countryDisabledOptions,
						})}
					/>

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

				<Section icon={icons.alignHorizontalVertical} label={__('Tracking', 'eightshift-forms')} collapsable>
					<TextControl
						label={<IconLabel icon={icons.googleTagManager} label={__('GTM tracking code', 'eightshift-forms')} />}
						value={countryTracking}
						onChange={(value) => setAttributes({ [getAttrKey('countryTracking', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('countryTracking', attributes, manifest), countryDisabledOptions)}
						className='es-no-field-spacing'
					/>
				</Section>

				<FieldOptionsMore
					{...props('field', attributes, {
						fieldDisabledOptions: countryDisabledOptions,
					})}
				/>
			</PanelBody>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: countryName,
					conditionalTagsIsHidden: checkAttr('countryFieldHidden', attributes, manifest),
				})}
			/>
		</>
	);
};
