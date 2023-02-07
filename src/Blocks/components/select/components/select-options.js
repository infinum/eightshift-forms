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
import { FieldOptions } from '../../../components/field/components/field-options';
import { FieldOptionsAdvanced } from '../../field/components/field-options-advanced';
import manifest from '../manifest.json';
import { isOptionDisabled, MissingName } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';

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
				<FieldOptions
					{...props('field', attributes, {
						fieldDisabledOptions: selectDisabledOptions,
					})}
				/>

				<TextControl
					label={<IconLabel icon={icons.fieldPlaceholder} label={__('Placeholder', 'eightshift-forms')} />}
					help={__('Shown when the field is empty', 'eightshift-forms')}
					value={selectPlaceholder}
					onChange={(value) => setAttributes({ [getAttrKey('selectPlaceholder', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('selectPlaceholder', attributes, manifest), selectDisabledOptions)}
				/>

				<FancyDivider label={__('Validation', 'eightshift-forms')} />

				<div className='es-h-spaced'>
					<Button
						icon={icons.fieldRequired}
						isPressed={selectIsRequired}
						onClick={() => setAttributes({ [getAttrKey('selectIsRequired', attributes, manifest)]: !selectIsRequired })}
						disabled={isOptionDisabled(getAttrKey('selectIsRequired', attributes, manifest), selectDisabledOptions)}
					>
						{__('Required', 'eightshift-forms')}
					</Button>
				</div>

				<FancyDivider label={__('Advanced', 'eightshift-forms')} />

				<TextControl
					label={<IconLabel icon={icons.fieldName} label={__('Name', 'eightshift-forms')} />}
					help={__('Should be unique! Used to identify the field within form submission data.', 'eightshift-forms')}
					value={selectName}
					onChange={(value) => setAttributes({ [getAttrKey('selectName', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('selectName', attributes, manifest), selectDisabledOptions)}
				/>
				<MissingName value={selectName} />

				<div className='es-h-spaced'>
					<Button
						icon={icons.fieldDisabled}
						isPressed={selectIsDisabled}
						onClick={() => setAttributes({ [getAttrKey('selectIsDisabled', attributes, manifest)]: !selectIsDisabled })}
						disabled={isOptionDisabled(getAttrKey('selectIsDisabled', attributes, manifest), selectDisabledOptions)}
					>
						{__('Disabled', 'eightshift-forms')}
					</Button>
				</div>

				<div className='es-h-spaced'>
					<Button
						icon={icons.fieldRequired}
						isPressed={selectUseSearch}
						onClick={() => setAttributes({ [getAttrKey('selectUseSearch', attributes, manifest)]: !selectUseSearch })}
						disabled={isOptionDisabled(getAttrKey('selectUseSearch', attributes, manifest), selectDisabledOptions)}
					>
						{__('Allow search', 'eightshift-forms')}
					</Button>
				</div>

				<FancyDivider label={__('Tracking', 'eightshift-forms')} />

				<TextControl
					label={<IconLabel icon={icons.code} label={__('GTM tracking code', 'eightshift-forms')} />}
					value={selectTracking}
					onChange={(value) => setAttributes({ [getAttrKey('selectTracking', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('selectTracking', attributes, manifest), selectDisabledOptions)}
				/>
			</PanelBody>

			<FieldOptionsAdvanced
				{...props('field', attributes)}
			/>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsParentName: selectName,
				})}
			/>
		</>
	);
};
