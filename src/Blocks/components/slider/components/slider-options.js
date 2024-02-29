import React from 'react';
import { useState } from '@wordpress/element';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { TextControl, PanelBody } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	props,
	Section,
	IconToggle,
	STORE_NAME,
} from '@eightshift/frontend-libs/scripts';
import { FieldOptions, FieldOptionsMore, FieldOptionsLayout, FieldOptionsVisibility } from '../../field/components/field-options';
import { isOptionDisabled, NameField } from '../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';

export const SliderOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('slider');

	const {
		setAttributes,
		title = __('Slider', 'eightshift-forms'),
	} = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const sliderName = checkAttr('sliderName', attributes, manifest);
	const sliderValue = checkAttr('sliderValue', attributes, manifest);
	const sliderIsDisabled = checkAttr('sliderIsDisabled', attributes, manifest);
	const sliderIsReadOnly = checkAttr('sliderIsReadOnly', attributes, manifest);
	const sliderIsRequired = checkAttr('sliderIsRequired', attributes, manifest);
	const sliderTracking = checkAttr('sliderTracking', attributes, manifest);
	const sliderDisabledOptions = checkAttr('sliderDisabledOptions', attributes, manifest);
	const sliderStartAmount = checkAttr('sliderStartAmount', attributes, manifest);
	const sliderEndAmount = checkAttr('sliderEndAmount', attributes, manifest);
	const sliderStepAmount = checkAttr('sliderStepAmount', attributes, manifest);

	return (
		<PanelBody title={title}>
			<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
				<NameField
					value={sliderName}
					attribute={getAttrKey('sliderName', attributes, manifest)}
					disabledOptions={sliderDisabledOptions}
					setAttributes={setAttributes}
					type={'slider'}
					isChanged={isNameChanged}
					setIsChanged={setIsNameChanged}
				/>

				<TextControl
					label={<IconLabel icon={icons.star} label={__('Start value', 'eightshift-forms')} />}
					value={sliderStartAmount}
					onChange={(value) => setAttributes({ [getAttrKey('sliderStartAmount', attributes, manifest)]: value })}
					type='number'
					disabled={isOptionDisabled(getAttrKey('sliderStartAmount', attributes, manifest), sliderDisabledOptions)}
				/>
				<TextControl
					label={<IconLabel icon={icons.star} label={__('End value', 'eightshift-forms')} />}
					value={sliderEndAmount}
					onChange={(value) => setAttributes({ [getAttrKey('sliderEndAmount', attributes, manifest)]: value })}
					type='number'
					disabled={isOptionDisabled(getAttrKey('sliderEndAmount', attributes, manifest), sliderDisabledOptions)}
				/>
				<TextControl
					label={<IconLabel icon={icons.star} label={__('Step value', 'eightshift-forms')} />}
					value={sliderStepAmount}
					onChange={(value) => setAttributes({ [getAttrKey('sliderStepAmount', attributes, manifest)]: value })}
					type='number'
					className='es-no-field-spacing'
					disabled={isOptionDisabled(getAttrKey('sliderStepAmount', attributes, manifest), sliderDisabledOptions)}
				/>
			</Section>

			<FieldOptions
				{...props('field', attributes, {
					fieldDisabledOptions: sliderDisabledOptions,
				})}
			/>

			<FieldOptionsLayout
				{...props('field', attributes, {
					fieldDisabledOptions: sliderDisabledOptions,
				})}
			/>

			<Section icon={icons.tools} label={__('Advanced', 'eightshift-forms')}>
				<TextControl
					label={<IconLabel icon={icons.titleGeneric} label={__('Initial value', 'eightshift-forms')} />}
					value={sliderValue}
					onChange={(value) => setAttributes({ [getAttrKey('sliderValue', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('sliderValue', attributes, manifest), sliderDisabledOptions)}
				/>

				<FieldOptionsVisibility
					{...props('field', attributes, {
						fieldDisabledOptions: sliderDisabledOptions,
					})}
				/>


				<IconToggle
					icon={icons.readOnly}
					label={__('Read-only', 'eightshift-forms')}
					checked={sliderIsReadOnly}
					onChange={(value) => setAttributes({ [getAttrKey('sliderIsReadOnly', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('sliderIsReadOnly', attributes, manifest), sliderDisabledOptions)}
				/>

				<IconToggle
					icon={icons.cursorDisabled}
					label={__('Disabled', 'eightshift-forms')}
					checked={sliderIsDisabled}
					onChange={(value) => setAttributes({ [getAttrKey('sliderIsDisabled', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('sliderIsDisabled', attributes, manifest), sliderDisabledOptions)}
					noBottomSpacing
				/>
			</Section>

			<Section icon={icons.checks} label={__('Validation', 'eightshift-forms')}>
				<IconToggle
					icon={icons.required}
					label={__('Required', 'eightshift-forms')}
					checked={sliderIsRequired}
					onChange={(value) => setAttributes({ [getAttrKey('sliderIsRequired', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('sliderIsRequired', attributes, manifest), sliderDisabledOptions)}
				/>
			</Section>

			<Section icon={icons.alignHorizontalVertical} label={__('Tracking', 'eightshift-forms')} collapsable>
				<TextControl
					label={<IconLabel icon={icons.googleTagManager} label={__('GTM tracking code', 'eightshift-forms')} />}
					value={sliderTracking}
					onChange={(value) => setAttributes({ [getAttrKey('sliderTracking', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('sliderTracking', attributes, manifest), sliderDisabledOptions)}
					className='es-no-field-spacing'
				/>
			</Section>

			<FieldOptionsMore
				{...props('field', attributes, {
					fieldDisabledOptions: sliderDisabledOptions,
				})}
			/>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: sliderName,
					conditionalTagsIsHidden: checkAttr('sliderFieldHidden', attributes, manifest),
				})}
			/>
		</PanelBody>
	);
};
