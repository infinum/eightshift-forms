import React from 'react';
import { useState } from '@wordpress/element';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { TextControl, PanelBody } from '@wordpress/components';
import { checkAttr, getAttrKey, props, icons, Section, IconToggle, IconLabel, STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { FieldOptions, FieldOptionsMore, FieldOptionsLayout, FieldOptionsVisibility } from '../../field/components/field-options';
import { isOptionDisabled, NameField } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';

export const RadiosOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('radios');

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
					<NameField
						value={radiosName}
						attribute={getAttrKey('radiosName', attributes, manifest)}
						disabledOptions={radiosDisabledOptions}
						setAttributes={setAttributes}
						type={'radios'}
						isChanged={isNameChanged}
						setIsChanged={setIsNameChanged}
					/>
				</Section>

				<FieldOptions
					{...props('field', attributes, {
						fieldDisabledOptions: radiosDisabledOptions,
					})}
				/>

				<FieldOptionsLayout
					{...props('field', attributes, {
						fieldDisabledOptions: radiosDisabledOptions,
					})}
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

				<Section icon={icons.tools} label={__('Advanced', 'eightshift-forms')}>
					<FieldOptionsVisibility
						{...props('field', attributes, {
							fieldDisabledOptions: radiosDisabledOptions,
						})}
					/>
				</Section>

				<Section icon={icons.alignHorizontalVertical} label={__('Tracking', 'eightshift-forms')} collapsable>
					<TextControl
						label={<IconLabel icon={icons.googleTagManager} label={__('GTM tracking code', 'eightshift-forms')} />}
						value={radiosTracking}
						onChange={(value) => setAttributes({ [getAttrKey('radiosTracking', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('radiosTracking', attributes, manifest), radiosDisabledOptions)}
						className='es-no-field-spacing'
					/>
				</Section>

				<FieldOptionsMore
					{...props('field', attributes, {
						fieldDisabledOptions: radiosDisabledOptions,
					})}
				/>
			</PanelBody>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: radiosName,
					conditionalTagsIsHidden: checkAttr('radiosFieldHidden', attributes, manifest),
				})}
			/>
		</>
	);
};
