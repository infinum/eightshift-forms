import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { TextControl, PanelBody } from '@wordpress/components';
import { icons, checkAttr, getAttrKey, IconLabel, props, IconToggle, Section, STORE_NAME } from '@eightshift/frontend-libs/scripts';
import { FieldOptionsMore, FieldOptionsLayout, FieldOptionsVisibility } from '../../field/components/field-options';
import { isOptionDisabled } from './../../utils';

export const SubmitOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('submit');

	const {
		setAttributes,
	} = attributes;

	const submitValue = checkAttr('submitValue', attributes, manifest);
	const submitIsDisabled = checkAttr('submitIsDisabled', attributes, manifest);
	const submitTracking = checkAttr('submitTracking', attributes, manifest);
	const submitDisabledOptions = checkAttr('submitDisabledOptions', attributes, manifest);

	return (
		<PanelBody title={__('Submit', 'eightshift-forms')}>
			<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
				<TextControl
					label={<IconLabel icon={icons.titleGeneric} label={__('Button label', 'eightshift-forms')} />}
					value={submitValue}
					onChange={(value) => setAttributes({ [getAttrKey('submitValue', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('submitValue', attributes, manifest), submitDisabledOptions)}
				/>
			</Section>

			<FieldOptionsLayout
				{...props('field', attributes, {
					fieldDisabledOptions: submitDisabledOptions,
				})}
			/>

			<Section icon={icons.tools} label={__('Advanced', 'eightshift-forms')}>
				<FieldOptionsVisibility
					{...props('field', attributes, {
						fieldDisabledOptions: submitDisabledOptions,
					})}
				/>

				<IconToggle
					icon={icons.cursorDisabled}
					label={__('Disabled', 'eightshift-forms')}
					checked={submitIsDisabled}
					onChange={(value) => setAttributes({ [getAttrKey('submitIsDisabled', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('submitIsDisabled', attributes, manifest), submitDisabledOptions)}
					noBottomSpacing
				/>
			</Section>

			<Section icon={icons.alignHorizontalVertical} label={__('Tracking', 'eightshift-forms')} collapsable>
				<TextControl
					label={<IconLabel icon={icons.googleTagManager} label={__('GTM tracking code', 'eightshift-forms')} />}
					value={submitTracking}
					onChange={(value) => setAttributes({ [getAttrKey('submitTracking', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('submitTracking', attributes, manifest), submitDisabledOptions)}
					className='es-no-field-spacing'
				/>
			</Section>

			<FieldOptionsMore
				{...props('field', attributes, {
					fieldDisabledOptions: submitDisabledOptions,
				})}
			/>
		</PanelBody>
	);
};
