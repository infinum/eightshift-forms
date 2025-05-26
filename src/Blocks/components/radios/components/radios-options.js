import React from 'react';
import { useState } from '@wordpress/element';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { icons } from '@eightshift/ui-components/icons';
import { checkAttr, getAttrKey, props, STORE_NAME } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldOptions, FieldOptionsMore, FieldOptionsLayout, FieldOptionsVisibility } from '../../field/components/field-options';
import { isOptionDisabled, NameField } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import { InputField, Select, BaseControl, Toggle } from '@eightshift/ui-components';

export const RadiosOptions = (attributes) => {
	const globalManifest = select(STORE_NAME).getSettings();
	const manifest = select(STORE_NAME).getComponent('radios');

	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const radiosName = checkAttr('radiosName', attributes, manifest);
	const radiosIsRequired = checkAttr('radiosIsRequired', attributes, manifest);
	const radiosDisabledOptions = checkAttr('radiosDisabledOptions', attributes, manifest);
	const radiosTracking = checkAttr('radiosTracking', attributes, manifest);
	const radiosShowAs = checkAttr('radiosShowAs', attributes, manifest);
	const radiosUseLabelAsPlaceholder = checkAttr('radiosUseLabelAsPlaceholder', attributes, manifest);
	const radiosPlaceholder = checkAttr('radiosPlaceholder', attributes, manifest);

	return (
		<>
			<BaseControl
				icon={icons.options}
				label={__('General', 'eightshift-forms')}
			>
				<NameField
					value={radiosName}
					attribute={getAttrKey('radiosName', attributes, manifest)}
					disabledOptions={radiosDisabledOptions}
					setAttributes={setAttributes}
					type={'radios'}
					isChanged={isNameChanged}
					setIsChanged={setIsNameChanged}
				/>
			</BaseControl>

			<Select
				icon={icons.optionListAlt}
				label={__('Show as', 'eightshift-forms')}
				value={radiosShowAs}
				options={globalManifest.showAsMap.options.filter((item) => item.value !== 'radios')}
				disabled={isOptionDisabled(getAttrKey('radiosShowAs', attributes, manifest), radiosDisabledOptions)}
				onChange={(value) => setAttributes({ [getAttrKey('radiosShowAs', attributes, manifest)]: value })}
				simpleValue
				inline
				noSearch
				clearable
				placeholder={__('Choose an alternative', 'eightshift-forms')}
			/>

			<FieldOptions
				{...props('field', attributes, {
					fieldDisabledOptions: radiosDisabledOptions,
				})}
			/>

			{radiosShowAs === 'select' && (
				<BaseControl
					icon={icons.fieldPlaceholder}
					label={__('Placeholder', 'eightshift-forms')}
				>
					{!radiosUseLabelAsPlaceholder && (
						<InputField
							help={__('Shown when the field is empty', 'eightshift-forms')}
							value={radiosPlaceholder}
							onChange={(value) => setAttributes({ [getAttrKey('radiosPlaceholder', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('radiosPlaceholder', attributes, manifest), radiosDisabledOptions)}
							className='es-no-field-spacing'
						/>
					)}
					<Toggle
						icon={icons.fieldPlaceholder}
						label={__('Use label as a placeholder', 'eightshift-forms')}
						checked={radiosUseLabelAsPlaceholder}
						onChange={(value) => {
							setAttributes({ [getAttrKey('radiosPlaceholder', attributes, manifest)]: undefined });
							setAttributes({ [getAttrKey('radiosUseLabelAsPlaceholder', attributes, manifest)]: value });
						}}
					/>
				</BaseControl>
			)}

			<FieldOptionsLayout
				{...props('field', attributes, {
					fieldDisabledOptions: radiosDisabledOptions,
				})}
			/>

			<BaseControl
				icon={icons.checks}
				label={__('Validation', 'eightshift-forms')}
			>
				<Toggle
					icon={icons.required}
					label={__('Required', 'eightshift-forms')}
					checked={radiosIsRequired}
					onChange={(value) => setAttributes({ [getAttrKey('radiosIsRequired', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('radiosIsRequired', attributes, manifest), radiosDisabledOptions)}
				/>
			</BaseControl>

			<BaseControl
				icon={icons.tools}
				label={__('Advanced', 'eightshift-forms')}
			>
				<FieldOptionsVisibility
					{...props('field', attributes, {
						fieldDisabledOptions: radiosDisabledOptions,
					})}
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
					value={radiosTracking}
					onChange={(value) => setAttributes({ [getAttrKey('radiosTracking', attributes, manifest)]: value })}
					disabled={isOptionDisabled(getAttrKey('radiosTracking', attributes, manifest), radiosDisabledOptions)}
					className='es-no-field-spacing'
				/>
			</BaseControl>

			<FieldOptionsMore
				{...props('field', attributes, {
					fieldDisabledOptions: radiosDisabledOptions,
				})}
			/>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: radiosName,
					conditionalTagsIsHidden: checkAttr('radiosFieldHidden', attributes, manifest),
				})}
			/>
		</>
	);
};
