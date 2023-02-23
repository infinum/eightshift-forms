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

export const CheckboxOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const checkboxLabel = checkAttr('checkboxLabel', attributes, manifest);
	const checkboxValue = checkAttr('checkboxValue', attributes, manifest);
	const checkboxIsChecked = checkAttr('checkboxIsChecked', attributes, manifest);
	const checkboxIsDisabled = checkAttr('checkboxIsDisabled', attributes, manifest);
	const checkboxIsReadOnly = checkAttr('checkboxIsReadOnly', attributes, manifest);
	const checkboxTracking = checkAttr('checkboxTracking', attributes, manifest);
	const checkboxDisabledOptions = checkAttr('checkboxDisabledOptions', attributes, manifest);

	return (
		<>
			<TextControl
				label={<IconLabel icon={icons.textUppercase} label={__('Checkbox label', 'eightshift-forms')} />}
				value={checkboxLabel}
				onChange={(value) => setAttributes({ [getAttrKey('checkboxLabel', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('checkboxLabel', attributes, manifest), checkboxDisabledOptions)}
			/>

			<div className='es-h-spaced'>
				<Button
					icon={icons.checkSquare}
					isPressed={checkboxIsChecked}
					onClick={() => setAttributes({ [getAttrKey('checkboxIsChecked', attributes, manifest)]: !checkboxIsChecked })}
					disabled={isOptionDisabled(getAttrKey('checkboxIsChecked', attributes, manifest), checkboxDisabledOptions)}
				>
					{__('Check by default', 'eightshift-forms')}
				</Button>
			</div>

			<FancyDivider label={__('Advanced', 'eightshift-forms')} />

			<TextControl
				label={<IconLabel icon={icons.fieldValue} label={__('Value', 'eightshift-forms')} />}
				help={__('Internal value, sent if checked.', 'eightshift-forms')}
				value={checkboxValue}
				onChange={(value) => setAttributes({ [getAttrKey('checkboxValue', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('checkboxValue', attributes, manifest), checkboxDisabledOptions)}
			/>

			<div className='es-h-spaced'>
				<Button
					icon={icons.fieldReadonly}
					isPressed={checkboxIsReadOnly}
					onClick={() => setAttributes({ [getAttrKey('checkboxIsReadOnly', attributes, manifest)]: !checkboxIsReadOnly })}
					disabled={isOptionDisabled(getAttrKey('checkboxIsReadOnly', attributes, manifest), checkboxDisabledOptions)}
				>
					{__('Read-only', 'eightshift-forms')}
				</Button>

				<Button
					icon={icons.fieldDisabled}
					isPressed={checkboxIsDisabled}
					onClick={() => setAttributes({ [getAttrKey('checkboxIsDisabled', attributes, manifest)]: !checkboxIsDisabled })}
					disabled={isOptionDisabled(getAttrKey('checkboxIsDisabled', attributes, manifest), checkboxDisabledOptions)}
				>
					{__('Disabled', 'eightshift-forms')}
				</Button>
			</div>

			<FancyDivider label={__('Tracking', 'eightshift-forms')} />

			<TextControl
				label={<IconLabel icon={icons.code} label={__('GTM tracking code', 'eightshift-forms')} />}
				value={checkboxTracking}
				onChange={(value) => setAttributes({ [getAttrKey('checkboxTracking', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('checkboxTracking', attributes, manifest), checkboxDisabledOptions)}
			/>
		</>
	);
};
