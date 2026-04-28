import React from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { Select, ContainerPanel, InputField, Toggle, Spacer } from '@eightshift/ui-components';
import { alignHorizontalVertical, checks, fieldPlaceholder, fieldRequired, googleTagManager, optionListAlt, options, tools } from '@eightshift/ui-components/icons';
import {
	FieldOptions,
	FieldOptionsMore,
	FieldOptionsLayout,
	FieldOptionsVisibility,
} from '../../field/components/field-options';
import { isOptionDisabled, NameField } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import manifest from '../manifest.json';
import globalManifest from '../../../manifest.json';

export const RadiosOptions = (attributes) => {
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
		<ContainerPanel>
			<Spacer
				border
				icon={options}
				text={__('General', 'eightshift-forms')}
			/>
			<NameField
				value={radiosName}
				attribute={getAttrKey('radiosName', attributes, manifest)}
				disabledOptions={radiosDisabledOptions}
				setAttributes={setAttributes}
				type={'radios'}
				isChanged={isNameChanged}
				setIsChanged={setIsNameChanged}
			/>

			<Select
				icon={optionListAlt}
				label={__('Show as', 'eightshift-forms')}
				value={radiosShowAs}
				options={globalManifest.showAsMap.options.filter((item) => item.value !== 'radios')}
				disabled={isOptionDisabled(getAttrKey('radiosShowAs', attributes, manifest), radiosDisabledOptions)}
				onChange={(value) => setAttributes({ [getAttrKey('radiosShowAs', attributes, manifest)]: value })}
				simpleValue
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
				<>
					{!radiosUseLabelAsPlaceholder && (
						<InputField
							help={__('Shown when the field is empty', 'eightshift-forms')}
							value={radiosPlaceholder}
							onChange={(value) => setAttributes({ [getAttrKey('radiosPlaceholder', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('radiosPlaceholder', attributes, manifest), radiosDisabledOptions)}
						/>
					)}
					<Toggle
						icon={fieldPlaceholder}
						label={__('Use label as a placeholder', 'eightshift-forms')}
						checked={radiosUseLabelAsPlaceholder}
						onChange={(value) => {
							setAttributes({ [getAttrKey('radiosPlaceholder', attributes, manifest)]: undefined });
							setAttributes({ [getAttrKey('radiosUseLabelAsPlaceholder', attributes, manifest)]: value });
						}}
					/>
				</>
			)}

			<FieldOptionsLayout
				{...props('field', attributes, {
					fieldDisabledOptions: radiosDisabledOptions,
				})}
			/>

			<Spacer
				border
				icon={tools}
				text={__('Advanced', 'eightshift-forms')}
			/>

			<FieldOptionsVisibility
				{...props('field', attributes, {
					fieldDisabledOptions: radiosDisabledOptions,
				})}
			/>

			<Spacer
				border
				icon={checks}
				text={__('Validation', 'eightshift-forms')}
			/>

			<Toggle
				icon={fieldRequired}
				label={__('Required', 'eightshift-forms')}
				checked={radiosIsRequired}
				onChange={(value) => setAttributes({ [getAttrKey('radiosIsRequired', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('radiosIsRequired', attributes, manifest), radiosDisabledOptions)}
			/>

			<Spacer
				border
				icon={alignHorizontalVertical}
				text={__('Tracking', 'eightshift-forms')}
			/>

			<InputField
				icon={googleTagManager}
				label={__('GTM tracking code', 'eightshift-forms')}
				placeholder={__('Enter GTM tracking code', 'eightshift-forms')}
				value={radiosTracking}
				onChange={(value) => setAttributes({ [getAttrKey('radiosTracking', attributes, manifest)]: value })}
				disabled={isOptionDisabled(getAttrKey('radiosTracking', attributes, manifest), radiosDisabledOptions)}
			/>

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
		</ContainerPanel>
	);
};
