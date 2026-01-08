import React, { useEffect } from 'react';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __, _n } from '@wordpress/i18n';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { AnimatedVisibility, Select, ContainerPanel, InputField, Toggle, Spacer } from '@eightshift/ui-components';
import {
	FieldOptions,
	FieldOptionsMore,
	FieldOptionsLayout,
	FieldOptionsVisibility,
} from '../../field/components/field-options';
import { icons } from '@eightshift/ui-components/icons';
import { isOptionDisabled, NameField } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import manifest from '../manifest.json';
import globalManifest from '../../../manifest.json';
import { NumberPicker } from '@eightshift/ui-components';

export const CheckboxesOptions = (attributes) => {
	const { options } = manifest;

	const { setAttributes, clientId } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const checkboxesName = checkAttr('checkboxesName', attributes, manifest);
	const checkboxesIsRequired = checkAttr('checkboxesIsRequired', attributes, manifest);
	const checkboxesIsRequiredCount = checkAttr('checkboxesIsRequiredCount', attributes, manifest);
	const checkboxesDisabledOptions = checkAttr('checkboxesDisabledOptions', attributes, manifest);
	const checkboxesShowAs = checkAttr('checkboxesShowAs', attributes, manifest);
	const checkboxesUseLabelAsPlaceholder = checkAttr('checkboxesUseLabelAsPlaceholder', attributes, manifest);
	const checkboxesPlaceholder = checkAttr('checkboxesPlaceholder', attributes, manifest);

	const [countInnerBlocks, setCountInnerBlocks] = useState(0);

	// Check if form selector has inner blocks.
	const countInnerBlocksCheck = useSelect((select) => {
		const { innerBlocks } = select('core/block-editor').getBlock(clientId);

		return innerBlocks.length;
	});

	// If parent block has inner blocks set internal state.
	useEffect(() => {
		setCountInnerBlocks(countInnerBlocksCheck);
	}, [countInnerBlocksCheck]);

	return (
		<ContainerPanel>
			<Spacer
				border
				icon={icons.options}
				text={__('General', 'eightshift-forms')}
			/>

			<NameField
				value={checkboxesName}
				attribute={getAttrKey('checkboxesName', attributes, manifest)}
				disabledOptions={checkboxesDisabledOptions}
				setAttributes={setAttributes}
				type={'checkboxes'}
				isChanged={isNameChanged}
				setIsChanged={setIsNameChanged}
			/>

			<Select
				icon={icons.optionListAlt}
				label={__('Show as', 'eightshift-forms')}
				value={checkboxesShowAs}
				options={globalManifest.showAsMap.options.filter((item) => item.value !== 'checkboxes')}
				disabled={isOptionDisabled(getAttrKey('checkboxesShowAs', attributes, manifest), checkboxesDisabledOptions)}
				onChange={(value) => setAttributes({ [getAttrKey('checkboxesShowAs', attributes, manifest)]: value })}
				simpleValue
				noSearch
				clearable
				placeholder={__('Choose an alternative', 'eightshift-forms')}
			/>

			<FieldOptions
				{...props('field', attributes, {
					fieldDisabledOptions: checkboxesDisabledOptions,
				})}
			/>

			{checkboxesShowAs === 'select' && (
				<>
					{!checkboxesUseLabelAsPlaceholder && (
						<InputField
							help={__('Shown when the field is empty', 'eightshift-forms')}
							value={checkboxesPlaceholder}
							onChange={(value) =>
								setAttributes({ [getAttrKey('checkboxesPlaceholder', attributes, manifest)]: value })
							}
							disabled={isOptionDisabled(
								getAttrKey('checkboxesPlaceholder', attributes, manifest),
								checkboxesDisabledOptions,
							)}
						/>
					)}
					<Toggle
						icon={icons.fieldPlaceholder}
						label={__('Use label as a placeholder', 'eightshift-forms')}
						checked={checkboxesUseLabelAsPlaceholder}
						onChange={(value) => {
							setAttributes({ [getAttrKey('checkboxesPlaceholder', attributes, manifest)]: undefined });
							setAttributes({ [getAttrKey('checkboxesUseLabelAsPlaceholder', attributes, manifest)]: value });
						}}
					/>
				</>
			)}

			<FieldOptionsLayout
				{...props('field', attributes, {
					fieldDisabledOptions: checkboxesDisabledOptions,
				})}
			/>

			<Spacer
				border
				icon={icons.tools}
				text={__('Advanced', 'eightshift-forms')}
			/>

			<FieldOptionsVisibility
				{...props('field', attributes, {
					fieldDisabledOptions: checkboxesDisabledOptions,
				})}
			/>

			<Spacer
				border
				icon={icons.checks}
				text={__('Validation', 'eightshift-forms')}
			/>

			<Toggle
				icon={icons.fieldRequired}
				label={__('Required', 'eightshift-forms')}
				checked={checkboxesIsRequired}
				onChange={(value) => {
					setAttributes({ [getAttrKey('checkboxesIsRequired', attributes, manifest)]: value });

					if (!value) {
						setAttributes({ [getAttrKey('checkboxesIsRequiredCount', attributes, manifest)]: 1 });
					}
				}}
			/>

			{checkboxesIsRequired && (
				<NumberPicker
					aria-label={__('At least', 'eightshift-forms')}
					value={checkboxesIsRequiredCount}
					onChange={(value) =>
						setAttributes({ [getAttrKey('checkboxesIsRequiredCount', attributes, manifest)]: value })
					}
					min={options.checkboxesIsRequiredCount.min}
					max={countInnerBlocks}
					disabled={isOptionDisabled(
						getAttrKey('checkboxesIsRequiredCount', attributes, manifest),
						checkboxesDisabledOptions,
					)}
					prefix={__('At least', 'eightshift-forms')}
					suffix={__('needs to be checked', 'eightshift-forms')}
				/>
			)}

			<FieldOptionsMore
				{...props('field', attributes, {
					fieldDisabledOptions: checkboxesDisabledOptions,
				})}
			/>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: checkboxesName,
					conditionalTagsIsHidden: checkAttr('checkboxesFieldHidden', attributes, manifest),
				})}
			/>
		</ContainerPanel>
	);
};
