import React from 'react';
import { __ } from '@wordpress/i18n';
import { TextControl, PanelBody } from '@wordpress/components';
import { icons, checkAttr, getAttrKey, IconLabel, props, Section, IconToggle } from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../../components/field/components/field-options';
import { FieldOptionsAdvanced } from '../../field/components/field-options-advanced';
import { isOptionDisabled, NameFieldLabel } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import manifest from '../manifest.json';

export const SelectOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const selectName = checkAttr('selectName', attributes, manifest);
	const selectIsDisabled = checkAttr('selectIsDisabled', attributes, manifest);
	const selectIsRequired = checkAttr('selectIsRequired', attributes, manifest);
	const selectTracking = checkAttr('selectTracking', attributes, manifest);
	const selectDisabledOptions = checkAttr('selectDisabledOptions', attributes, manifest);
	const selectUseSearch = checkAttr('selectUseSearch', attributes, manifest);
	const selectPlaceholder = checkAttr('selectPlaceholder', attributes, manifest);

	return (
		<>
			<PanelBody title={__('Select', 'eightshift-forms')}>
				<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
					<TextControl
						label={<IconLabel icon={icons.fieldPlaceholder} label={__('Placeholder', 'eightshift-forms')} />}
						help={__('Shown when the field is empty', 'eightshift-forms')}
						value={selectPlaceholder}
						onChange={(value) => setAttributes({ [getAttrKey('selectPlaceholder', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('selectPlaceholder', attributes, manifest), selectDisabledOptions)}
						className='es-no-field-spacing'
					/>
				</Section>

				<FieldOptions
					{...props('field', attributes, {
						fieldDisabledOptions: selectDisabledOptions,
					})}
					additionalControls={<FieldOptionsAdvanced {...props('field', attributes)} />}
				/>

				<Section icon={icons.checks} label={__('Validation', 'eightshift-forms')}>
					<IconToggle
						icon={icons.required}
						label={__('Required', 'eightshift-forms')}
						checked={selectIsRequired}
						onChange={(value) => setAttributes({ [getAttrKey('selectIsRequired', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('selectIsRequired', attributes, manifest), selectDisabledOptions)}
						noBottomSpacing
					/>
				</Section>

				<Section icon={icons.tools} label={__('Advanced', 'eightshift-forms')}>
					<TextControl
						label={<NameFieldLabel value={selectName} />}
						help={__('Identifies the field within form submission data. Should be unique.', 'eightshift-forms')}
						value={selectName}
						onChange={(value) => setAttributes({ [getAttrKey('selectName', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('selectName', attributes, manifest), selectDisabledOptions)}
					/>

					<IconToggle
						icon={icons.cursorDisabled}
						label={__('Disabled', 'eightshift-forms')}
						checked={selectIsDisabled}
						onChange={(value) => setAttributes({ [getAttrKey('selectIsDisabled', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('selectIsDisabled', attributes, manifest), selectDisabledOptions)}
					/>

					<IconToggle
						icon={icons.search}
						label={__('Search', 'eightshift-forms')}
						checked={selectUseSearch}
						onChange={(value) => setAttributes({ [getAttrKey('selectUseSearch', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('selectUseSearch', attributes, manifest), selectDisabledOptions)}
						noBottomSpacing
					/>
				</Section>

				<Section icon={icons.alignHorizontalVertical} label={__('Tracking', 'eightshift-forms')} noBottomSpacing>
					<TextControl
						label={<IconLabel icon={icons.googleTagManager} label={__('GTM tracking code', 'eightshift-forms')} />}
						value={selectTracking}
						onChange={(value) => setAttributes({ [getAttrKey('selectTracking', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('selectTracking', attributes, manifest), selectDisabledOptions)}
						className='es-no-field-spacing'
					/>
				</Section>
			</PanelBody>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsParentName: selectName,
				})}
			/>
		</>
	);
};
