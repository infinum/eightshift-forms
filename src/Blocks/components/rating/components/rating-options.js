import React from 'react';
import { useState } from '@wordpress/element';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { icons } from '@eightshift/ui-components/icons';
import { checkAttr, getAttrKey, props, STORE_NAME } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldOptions, FieldOptionsMore, FieldOptionsLayout, FieldOptionsVisibility } from '../../field/components/field-options';
import { isOptionDisabled, NameField } from '../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import { InputField, BaseControl, Toggle, ContainerPanel } from '@eightshift/ui-components';

export const RatingOptions = (attributes) => {
	const manifest = select(STORE_NAME).getComponent('rating');

	const { setAttributes, title = __('Rating', 'eightshift-forms') } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const ratingName = checkAttr('ratingName', attributes, manifest);
	const ratingValue = checkAttr('ratingValue', attributes, manifest);
	const ratingIsDisabled = checkAttr('ratingIsDisabled', attributes, manifest);
	const ratingIsReadOnly = checkAttr('ratingIsReadOnly', attributes, manifest);
	const ratingIsRequired = checkAttr('ratingIsRequired', attributes, manifest);
	const ratingTracking = checkAttr('ratingTracking', attributes, manifest);
	const ratingDisabledOptions = checkAttr('ratingDisabledOptions', attributes, manifest);
	const ratingAmount = checkAttr('ratingAmount', attributes, manifest);

	return (
		<ContainerPanel title={title}>
			<BaseControl
				icon={icons.options}
				label={__('General', 'eightshift-forms')}
			>
				<NameField
					value={ratingName}
					attribute={getAttrKey('ratingName', attributes, manifest)}
					disabledOptions={ratingDisabledOptions}
					setAttributes={setAttributes}
					type={'rating'}
					isChanged={isNameChanged}
					setIsChanged={setIsNameChanged}
				/>

				<InputField
					icon={icons.star}
					label={__('Amount of stars', 'eightshift-forms')}
					value={ratingAmount}
					onChange={(value) => setAttributes({ [getAttrKey('ratingAmount', attributes, manifest)]: value })}
					min={1}
					max={10}
					type='number'
					className='es-no-field-spacing'
					disabled={isOptionDisabled(getAttrKey('ratingAmount', attributes, manifest), ratingDisabledOptions)}
				/>
			</BaseControl>

			<FieldOptions
				{...props('field', attributes, {
					fieldDisabledOptions: ratingDisabledOptions,
				})}
			/>

			<FieldOptionsLayout
				{...props('field', attributes, {
					fieldDisabledOptions: ratingDisabledOptions,
				})}
			/>

			<BaseControl
				icon={icons.tools}
				label={__('Advanced', 'eightshift-forms')}
			>
				<InputField
					icon={icons.titleGeneric}
					label={__('Initial value', 'eightshift-forms')}
					value={ratingValue}
					onChange={(value) => setAttributes({ [getAttrKey('ratingValue', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('ratingValue', attributes, manifest), ratingDisabledOptions)}
				/>

				<FieldOptionsVisibility
					{...props('field', attributes, {
						fieldDisabledOptions: ratingDisabledOptions,
					})}
				/>

				<Toggle
					icon={icons.readOnly}
					label={__('Read-only', 'eightshift-forms')}
					checked={ratingIsReadOnly}
					onChange={(value) => setAttributes({ [getAttrKey('ratingIsReadOnly', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('ratingIsReadOnly', attributes, manifest), ratingDisabledOptions)}
				/>

				<Toggle
					icon={icons.cursorDisabled}
					label={__('Disabled', 'eightshift-forms')}
					checked={ratingIsDisabled}
					onChange={(value) => setAttributes({ [getAttrKey('ratingIsDisabled', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('ratingIsDisabled', attributes, manifest), ratingDisabledOptions)}
				/>
			</BaseControl>

			<BaseControl
				icon={icons.checks}
				label={__('Validation', 'eightshift-forms')}
			>
				<Toggle
					icon={icons.required}
					label={__('Required', 'eightshift-forms')}
					checked={ratingIsRequired}
					onChange={(value) => setAttributes({ [getAttrKey('ratingIsRequired', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('ratingIsRequired', attributes, manifest), ratingDisabledOptions)}
				/>
			</BaseControl>

			<BaseControl
				icon={icons.alignHorizontalVertical}
				label={__('Tracking', 'eightshift-forms')}
				collapsable
			>
				<InputField
					icon={icons.googleTagManager}
					label={__('GTM tracking code', 'eightshift-forms')}
					value={ratingTracking}
					onChange={(value) => setAttributes({ [getAttrKey('ratingTracking', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('ratingTracking', attributes, manifest), ratingDisabledOptions)}
					className='es-no-field-spacing'
				/>
			</BaseControl>

			<FieldOptionsMore
				{...props('field', attributes, {
					fieldDisabledOptions: ratingDisabledOptions,
				})}
			/>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: ratingName,
					conditionalTagsIsHidden: checkAttr('ratingFieldHidden', attributes, manifest),
				})}
			/>
		</ContainerPanel>
	);
};
