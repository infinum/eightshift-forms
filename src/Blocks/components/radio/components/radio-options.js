import React from 'react';
import { __ } from '@wordpress/i18n';
import { TextControl, Button } from '@wordpress/components';
import {
	checkAttr,
	getAttrKey,
	icons,
	IconLabel,
	FancyDivider,
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';
import { isOptionDisabled } from './../../utils';

export const RadioOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const radioLabel = checkAttr('radioLabel', attributes, manifest);
	const radioValue = checkAttr('radioValue', attributes, manifest);
	const radioIsChecked = checkAttr('radioIsChecked', attributes, manifest);
	const radioIsDisabled = checkAttr('radioIsDisabled', attributes, manifest);
	const radioTracking = checkAttr('radioTracking', attributes, manifest);
	const radioDisabledOptions = checkAttr('radioDisabledOptions', attributes, manifest);

	return (
		<>
			<TextControl
				label={<IconLabel icon={icons.textSize} label={__('Radio button label', 'eightshift-forms')} />}
				value={radioLabel}
				onChange={(value) => setAttributes({ [getAttrKey('radioLabel', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('radioLabel', attributes, manifest), radioDisabledOptions)}
			/>

			<Button
				icon={icons.checkCircle}
				isPressed={radioIsChecked}
				onClick={() => setAttributes({ [getAttrKey('radioIsChecked', attributes, manifest)]: !radioIsChecked })}
				disabled={isOptionDisabled(getAttrKey('radioIsChecked', attributes, manifest), radioDisabledOptions)}
			>
				{__('Select by default', 'eightshift-forms')}
			</Button>

			<FancyDivider label={__('Advanced', 'eightshift-forms')} />

			<TextControl
				label={<IconLabel icon={icons.fieldValue} label={__('Value', 'eightshift-forms')} />}
				help={__('Internal value, sent if the radio button is selected.', 'eightshift-forms')}
				value={radioValue}
				onChange={(value) => setAttributes({ [getAttrKey('radioValue', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('radioValue', attributes, manifest), radioDisabledOptions)}
			/>

			<Button
				icon={icons.fieldDisabled}
				isPressed={radioIsDisabled}
				onClick={() => setAttributes({ [getAttrKey('radioIsDisabled', attributes, manifest)]: !radioIsDisabled })}
				disabled={isOptionDisabled(getAttrKey('radioIsDisabled', attributes, manifest), radioDisabledOptions)}
			>
				{__('Disabled', 'eightshift-forms')}
			</Button>

			<FancyDivider label={__('Tracking', 'eightshift-forms')} />

			<TextControl
				label={<IconLabel icon={icons.code} label={__('GTM tracking code', 'eightshift-forms')} />}
				value={radioTracking}
				onChange={(value) => setAttributes({ [getAttrKey('radioTracking', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('radioTracking', attributes, manifest), radioDisabledOptions)}
			/>
		</>
	);
};
