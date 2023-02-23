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
		<>
			<PanelBody title={__('Submit', 'eightshift-forms')}>
				<FieldOptions
					{...props('field', attributes, {
						fieldDisabledOptions: submitDisabledOptions,
					})}
					showFieldLabel={false}
				/>

				<TextControl
					label={<IconLabel icon={icons.buttonOutline} label={__('Button label', 'eightshift-forms')} />}
					value={submitValue}
					onChange={(value) => setAttributes({ [getAttrKey('submitValue', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('submitValue', attributes, manifest), submitDisabledOptions)}
				/>

				<Button
					icon={icons.fieldDisabled}
					isPressed={submitIsDisabled}
					onClick={() => setAttributes({ [getAttrKey('submitIsDisabled', attributes, manifest)]: !submitIsDisabled })}
					disabled={isOptionDisabled(getAttrKey('submitIsDisabled', attributes, manifest), submitDisabledOptions)}
				>
					{__('Disabled', 'eightshift-forms')}
				</Button>

				<FancyDivider label={__('Tracking', 'eightshift-forms')} />

				<TextControl
					label={<IconLabel icon={icons.code} label={__('GTM tracking code', 'eightshift-forms')} />}
					value={submitTracking}
					onChange={(value) => setAttributes({ [getAttrKey('submitTracking', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('submitTracking', attributes, manifest), submitDisabledOptions)}
				/>
			</PanelBody>

			<FieldOptionsAdvanced
				{...props('field', attributes)}
			/>
		</>
	);
};
