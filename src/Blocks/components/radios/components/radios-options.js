import React from 'react';
import { __ } from '@wordpress/i18n';
import { TextControl, PanelBody, Button } from '@wordpress/components';
import {
	checkAttr,
	getAttrKey,
	props,
	icons,
	IconLabel,
	FancyDivider,
} from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../field/components/field-options';
import { FieldOptionsAdvanced } from '../../field/components/field-options-advanced';
import manifest from '../manifest.json';
import { isOptionDisabled, MissingName } from './../../utils';
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
				<FieldOptions
					{...props('field', attributes, {
						fieldDisabledOptions: radiosDisabledOptions,
					})}
				/>

				<FancyDivider label={__('Validation', 'eightshift-forms')} />

				<Button
					icon={icons.fieldRequired}
					isPressed={radiosIsRequired}
					onClick={() => setAttributes({ [getAttrKey('radiosIsRequired', attributes, manifest)]: !radiosIsRequired })}
					disabled={isOptionDisabled(getAttrKey('radiosIsRequired', attributes, manifest), radiosDisabledOptions)}
				>
					{__('Required', 'eightshift-forms')}
				</Button>

				<FancyDivider label={__('Advanced', 'eightshift-forms')} />

				<TextControl
					label={<IconLabel icon={icons.fieldName} label={__('Name', 'eightshift-forms')} />}
					help={__('Should be unique! Used to identify the field within form submission data.', 'eightshift-forms')}
					value={radiosName}
					onChange={(value) => setAttributes({ [getAttrKey('radiosName', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('radiosName', attributes, manifest), radiosDisabledOptions)}
				/>
				<MissingName value={radiosName} />

			</PanelBody>

			<FieldOptionsAdvanced
				{...props('field', attributes)}
			/>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsParentName: radiosName,
				})}
			/>
		</>
	);
};
