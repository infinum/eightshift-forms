import React from 'react';
import { __ } from '@wordpress/i18n';
import { TextControl, PanelBody } from '@wordpress/components';
import { icons, checkAttr, getAttrKey, IconLabel, props, IconToggle, Section } from '@eightshift/frontend-libs/scripts';
import { FieldOptions } from '../../../components/field/components/field-options';
import { FieldOptionsAdvanced } from '../../field/components/field-options-advanced';
import manifest from '../manifest.json';
import { isOptionDisabled } from './../../utils';

export const SubmitOptions = (attributes) => {
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

				<IconToggle
					icon={icons.cursorDisabled}
					label={__('Disabled', 'eightshift-forms')}
					checked={submitIsDisabled}
					onChange={(value) => setAttributes({ [getAttrKey('submitIsDisabled', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('submitIsDisabled', attributes, manifest), submitDisabledOptions)}
					noBottomSpacing
				/>
			</Section>

			<FieldOptions
				{...props('field', attributes, {
					fieldDisabledOptions: submitDisabledOptions,
				})}
				showFieldLabel={false}
				additionalControls={<FieldOptionsAdvanced {...props('field', attributes)} />}
			/>

			<Section icon={icons.alignHorizontalVertical} label={__('Tracking', 'eightshift-forms')} noBottomSpacing>
				<TextControl
					label={<IconLabel icon={icons.googleTagManager} label={__('GTM tracking code', 'eightshift-forms')} />}
					value={submitTracking}
					onChange={(value) => setAttributes({ [getAttrKey('submitTracking', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('submitTracking', attributes, manifest), submitDisabledOptions)}
					className='es-no-field-spacing'
				/>
			</Section>
		</PanelBody>
	);
};
