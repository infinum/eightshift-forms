import React from 'react';
import { __ } from '@wordpress/i18n';
import { TextControl } from '@wordpress/components';
import { checkAttr, getAttrKey, icons, IconLabel, IconToggle, Section } from '@eightshift/frontend-libs/scripts';
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
			<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
				<TextControl
					label={<IconLabel icon={icons.tag} label={__('Label', 'eightshift-forms')} />}
					value={radioLabel}
					onChange={(value) => setAttributes({ [getAttrKey('radioLabel', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('radioLabel', attributes, manifest), radioDisabledOptions)}
				/>

				<IconToggle
					icon={icons.checkCircle}
					label={__('Selected', 'eightshift-forms')}
					checked={radioIsChecked}
					onChange={(value) => setAttributes({ [getAttrKey('radioIsChecked', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('radioIsChecked', attributes, manifest), radioDisabledOptions)}
					noBottomSpacing
				/>
			</Section>

			<Section icon={icons.tools} label={__('Advanced', 'eightshift-forms')}>
				<TextControl
					label={<IconLabel icon={icons.textWrite} label={__('Value', 'eightshift-forms')} />}
					help={__('Internal value, sent if selected.', 'eightshift-forms')}
					value={radioValue}
					onChange={(value) => setAttributes({ [getAttrKey('radioValue', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('radioValue', attributes, manifest), radioDisabledOptions)}
				/>

				<IconToggle
					icon={icons.cursorDisabled}
					label={__('Disabled', 'eightshift-forms')}
					checked={radioIsDisabled}
					onChange={(value) => setAttributes({ [getAttrKey('radioIsDisabled', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('radioIsDisabled', attributes, manifest), radioDisabledOptions)}
					noBottomSpacing
				/>
			</Section>

			<Section icon={icons.alignHorizontalVertical} label={__('Tracking', 'eightshift-forms')} noBottomSpacing>
				<TextControl
					label={<IconLabel icon={icons.googleTagManager} label={__('GTM tracking code', 'eightshift-forms')} />}
					value={radioTracking}
					onChange={(value) => setAttributes({ [getAttrKey('radioTracking', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('radioTracking', attributes, manifest), radioDisabledOptions)}
					className='es-no-field-spacing'
				/>
			</Section>
		</>
	);
};
