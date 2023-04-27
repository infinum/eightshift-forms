import React from 'react';
import { __ } from '@wordpress/i18n';
import { TextControl } from '@wordpress/components';
import { checkAttr, getAttrKey, icons, IconLabel, IconToggle, Section } from '@eightshift/frontend-libs/scripts';
import { isOptionDisabled } from './../../utils';
import manifest from '../manifest.json';

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
			<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
				<TextControl
					label={<IconLabel icon={icons.tag} label={__('Label', 'eightshift-forms')} />}
					value={checkboxLabel}
					onChange={(value) => setAttributes({ [getAttrKey('checkboxLabel', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('checkboxLabel', attributes, manifest), checkboxDisabledOptions)}
				/>

				<IconToggle
					icon={icons.checkSquare}
					label={__('Checked', 'eightshift-forms')}
					checked={checkboxIsChecked}
					onChange={(value) => setAttributes({ [getAttrKey('checkboxIsChecked', attributes, manifest)]: value })}
					noBottomSpacing
				/>
			</Section>

			<Section icon={icons.tools} label={__('Advanced', 'eightshift-forms')}>
				<TextControl
					label={<IconLabel icon={icons.textWrite} label={__('Value', 'eightshift-forms')} />}
					help={__('Internal value, sent if checked.', 'eightshift-forms')}
					value={checkboxValue}
					onChange={(value) => setAttributes({ [getAttrKey('checkboxValue', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('checkboxValue', attributes, manifest), checkboxDisabledOptions)}
				/>

				<IconToggle
					icon={icons.readOnly}
					label={__('Read-only', 'eightshift-forms')}
					checked={checkboxIsReadOnly}
					onChange={(value) => setAttributes({ [getAttrKey('checkboxIsReadOnly', attributes, manifest)]: value })}
				/>

				<IconToggle
					icon={icons.cursorDisabled}
					label={__('Disabled', 'eightshift-forms')}
					checked={checkboxIsDisabled}
					onChange={(value) => setAttributes({ [getAttrKey('checkboxIsDisabled', attributes, manifest)]: value })}
					noBottomSpacing
				/>
			</Section>

			<Section icon={icons.alignHorizontalVertical} label={__('Tracking', 'eightshift-forms')} noBottomSpacing>
				<TextControl
					label={<IconLabel icon={icons.googleTagManager} label={__('GTM tracking code', 'eightshift-forms')} />}
					value={checkboxTracking}
					onChange={(value) => setAttributes({ [getAttrKey('checkboxTracking', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('checkboxTracking', attributes, manifest), checkboxDisabledOptions)}
					className='es-no-field-spacing'
				/>
			</Section>
		</>
	);
};
