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

export const SelectOptionOptions = (attributes) => {
	const {
		setAttributes,
	} = attributes;

	const selectOptionLabel = checkAttr('selectOptionLabel', attributes, manifest);
	const selectOptionValue = checkAttr('selectOptionValue', attributes, manifest);
	const selectOptionIsSelected = checkAttr('selectOptionIsSelected', attributes, manifest);
	const selectOptionIsDisabled = checkAttr('selectOptionIsDisabled', attributes, manifest);
	const selectOptionDisabledOptions = checkAttr('selectOptionDisabledOptions', attributes, manifest);

	return (
		<>
			<TextControl
				label={<IconLabel icon={icons.textSize} label={__('Option label', 'eightshift-forms')} />}
				value={selectOptionLabel}
				onChange={(value) => setAttributes({ [getAttrKey('selectOptionLabel', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('selectOptionLabel', attributes, manifest), selectOptionDisabledOptions)}
			/>

			<div className='es-h-spaced'>
				<Button
					icon={icons.checkSquare}
					isPressed={selectOptionIsSelected}
					onClick={() => setAttributes({ [getAttrKey('selectOptionIsSelected', attributes, manifest)]: !selectOptionIsSelected })}
					disabled={isOptionDisabled(getAttrKey('selectOptionIsSelected', attributes, manifest), selectOptionDisabledOptions)}
				>
					{__('Select by default', 'eightshift-forms')}
				</Button>
			</div>

			<FancyDivider label={__('Advanced', 'eightshift-forms')} />

			<TextControl
				label={<IconLabel icon={icons.fieldValue} label={__('Value', 'eightshift-forms')} />}
				help={__('Internal value, sent if the option is selected', 'eightshift-forms')}
				value={selectOptionValue}
				onChange={(value) => setAttributes({ [getAttrKey('selectOptionValue', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('selectOptionValue', attributes, manifest), selectOptionDisabledOptions)}
			/>

			<div className='es-h-spaced'>
				<Button
					icon={icons.fieldDisabled}
					isPressed={selectOptionIsDisabled}
					onClick={() => setAttributes({ [getAttrKey('selectOptionIsDisabled', attributes, manifest)]: !selectOptionIsDisabled })}
					disabled={isOptionDisabled(getAttrKey('selectOptionIsDisabled', attributes, manifest), selectOptionDisabledOptions)}
				>
					{__('Disabled', 'eightshift-forms')}
				</Button>
			</div>
		</>
	);
};
