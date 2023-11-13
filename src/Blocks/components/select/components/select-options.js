import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { TextControl, PanelBody, Button } from '@wordpress/components';
import {
	icons,
	checkAttr,
	getAttrKey,
	IconLabel,
	props,
	Section,
	IconToggle,
	STORE_NAME,
	NumberPicker,
	Control,
	Select,
} from '@eightshift/frontend-libs/scripts';
import { FieldOptions, FieldOptionsMore, FieldOptionsLayout, FieldOptionsVisibility } from '../../field/components/field-options';
import { isOptionDisabled, NameField } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';

export const SelectOptions = (attributes) => {
	const globalManifest = select(STORE_NAME).getSettings();
	const manifest = select(STORE_NAME).getComponent('select');

	const {
		options,
	} = manifest;

	const {
		setAttributes,
	} = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const selectName = checkAttr('selectName', attributes, manifest);
	const selectIsDisabled = checkAttr('selectIsDisabled', attributes, manifest);
	const selectIsRequired = checkAttr('selectIsRequired', attributes, manifest);
	const selectTracking = checkAttr('selectTracking', attributes, manifest);
	const selectDisabledOptions = checkAttr('selectDisabledOptions', attributes, manifest);
	const selectUseSearch = checkAttr('selectUseSearch', attributes, manifest);
	const selectPlaceholder = checkAttr('selectPlaceholder', attributes, manifest);
	const selectUseLabelAsPlaceholder = checkAttr('selectUseLabelAsPlaceholder', attributes, manifest);
	const selectIsMultiple = checkAttr('selectIsMultiple', attributes, manifest);
	const selectMinCount = checkAttr('selectMinCount', attributes, manifest);
	const selectMaxCount = checkAttr('selectMaxCount', attributes, manifest);
	const selectShowAs = checkAttr('selectShowAs', attributes, manifest);

	return (
		<>
			<PanelBody title={__('Select', 'eightshift-forms')}>
				<Section icon={icons.options} label={__('General', 'eightshift-forms')}>
					<NameField
						value={selectName}
						attribute={getAttrKey('selectName', attributes, manifest)}
						disabledOptions={selectDisabledOptions}
						setAttributes={setAttributes}
						type={'select'}
						isChanged={isNameChanged}
						setIsChanged={setIsNameChanged}
					/>
				</Section>

				<Select
					icon={icons.optionListAlt}
					label={__('Show as', 'eightshift-forms')}
					value={selectShowAs}
					options={globalManifest.showAsMap.options.filter((item) => item.value !== 'select')}
					disabled={isOptionDisabled(getAttrKey('selectShowAs', attributes, manifest), selectDisabledOptions)}
					onChange={(value) => {
						setAttributes({ [getAttrKey('selectShowAs', attributes, manifest)]: value });
					}}
					additionalSelectClasses='es-w-40'
					simpleValue
					inlineLabel
					noSearch
					clearable
				/>

				<FieldOptions
					{...props('field', attributes, {
						fieldDisabledOptions: selectDisabledOptions,
					})}
					showFieldHideLabel={false}
				/>

				<Section icon={icons.fieldPlaceholder} label={__('Placeholder', 'eightshift-forms')}>
					{!selectUseLabelAsPlaceholder &&
						<TextControl
							help={__('Shown when the field is empty', 'eightshift-forms')}
							value={selectPlaceholder}
							onChange={(value) => setAttributes({ [getAttrKey('selectPlaceholder', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('selectPlaceholder', attributes, manifest), selectDisabledOptions)}
							className='es-no-field-spacing'
						/>
					}
					<IconToggle
						icon={icons.fieldPlaceholder}
						label={__('Use label as placeholder', 'eightshift-forms')}
						checked={selectUseLabelAsPlaceholder}
						onChange={(value) => {
							setAttributes({ [getAttrKey('selectPlaceholder', attributes, manifest)]: undefined });
							setAttributes({ [getAttrKey('selectUseLabelAsPlaceholder', attributes, manifest)]: value });
						}}
					/>
				</Section>

				<FieldOptionsLayout
					{...props('field', attributes, {
						fieldDisabledOptions: selectDisabledOptions,
					})}
				/>

				<Section icon={icons.checks} label={__('Validation', 'eightshift-forms')}>
					<IconToggle
						icon={icons.required}
						label={__('Required', 'eightshift-forms')}
						checked={selectIsRequired}
						onChange={(value) => setAttributes({ [getAttrKey('selectIsRequired', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('selectIsRequired', attributes, manifest), selectDisabledOptions)}
					/>

					{selectIsMultiple &&
						<Control
							icon={icons.range}
							label={__('Number of items', 'eightshift-forms')}
							additionalLabelClasses='es-mb-0!'
						>
							<div className='es-h-spaced es-gap-5!'>
								<div className='es-display-flex es-items-end es-gap-2'>
									<NumberPicker
										label={__('Min', 'eightshift-forms')}
										value={selectMinCount}
										onChange={(value) => setAttributes({ [getAttrKey('selectMinCount', attributes, manifest)]: value })}
										min={options.selectMinCount.min}
										step={options.selectMinCount.step}
										disabled={isOptionDisabled(getAttrKey('selectMinCount', attributes, manifest), selectDisabledOptions)}
										placeholder='–'
										fixedWidth={4}
										noBottomSpacing
									/>

									{selectMinCount > 0 && !isOptionDisabled(getAttrKey('selectMinCount', attributes, manifest), selectDisabledOptions) &&
										<Button
											label={__('Disable', 'eightshift-forms')}
											icon={icons.clear}
											onClick={() => setAttributes({ [getAttrKey('selectMinCount', attributes, manifest)]: undefined })}
											className='es-button-square-32 es-button-icon-24'
											showTooltip
										/>
									}
								</div>

								<div className='es-display-flex es-items-end es-gap-2'>
									<NumberPicker
										label={__('Max', 'eightshift-forms')}
										value={selectMaxCount}
										onChange={(value) => setAttributes({ [getAttrKey('selectMaxCount', attributes, manifest)]: value })}
										min={options.selectMaxCount.min}
										step={options.selectMaxCount.step}
										disabled={isOptionDisabled(getAttrKey('selectMaxCount', attributes, manifest), selectDisabledOptions)}
										placeholder='–'
										fixedWidth={4}
										noBottomSpacing
									/>

									{selectMaxCount > 0 && !isOptionDisabled(getAttrKey('selectMaxCount', attributes, manifest), selectDisabledOptions) &&
										<Button
											label={__('Disable', 'eightshift-forms')}
											icon={icons.clear}
											onClick={() => setAttributes({ [getAttrKey('selectMaxCount', attributes, manifest)]: undefined })}
											className='es-button-square-32 es-button-icon-24'
											showTooltip
										/>
									}
								</div>
							</div>
						</Control>
					}

				</Section>

				<Section icon={icons.tools} label={__('Advanced', 'eightshift-forms')}>
					<FieldOptionsVisibility
						{...props('field', attributes, {
							fieldDisabledOptions: selectDisabledOptions,
						})}
					/>

					<IconToggle
						icon={icons.cursorDisabled}
						label={__('Disabled', 'eightshift-forms')}
						checked={selectIsDisabled}
						onChange={(value) => setAttributes({ [getAttrKey('selectIsDisabled', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('selectIsDisabled', attributes, manifest), selectDisabledOptions)}
					/>

					<IconToggle
						icon={icons.search}
						label={__('Search', 'eightshift-forms')}
						checked={selectUseSearch}
						onChange={(value) => setAttributes({ [getAttrKey('selectUseSearch', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('selectUseSearch', attributes, manifest), selectDisabledOptions)}
					/>

					<IconToggle
						icon={icons.files}
						label={__('Allow multi selection', 'eightshift-forms')}
						checked={selectIsMultiple}
						onChange={(value) => {
							setAttributes({ [getAttrKey('selectIsMultiple', attributes, manifest)]: value });
							setAttributes({ [getAttrKey('selectMaxCount', attributes, manifest)]: undefined });
							setAttributes({ [getAttrKey('selectMinCount', attributes, manifest)]: undefined });
						}}
						disabled={isOptionDisabled(getAttrKey('selectIsMultiple', attributes, manifest), selectDisabledOptions)}
						noBottomSpacing
					/>
				</Section>

				<Section icon={icons.alignHorizontalVertical} label={__('Tracking', 'eightshift-forms')} collapsable>
					<TextControl
						label={<IconLabel icon={icons.googleTagManager} label={__('GTM tracking code', 'eightshift-forms')} />}
						value={selectTracking}
						onChange={(value) => setAttributes({ [getAttrKey('selectTracking', attributes, manifest)]: value })}
						disabled={isOptionDisabled(getAttrKey('selectTracking', attributes, manifest), selectDisabledOptions)}
						className='es-no-field-spacing'
					/>
				</Section>

				<FieldOptionsMore
					{...props('field', attributes, {
						fieldDisabledOptions: selectDisabledOptions,
					})}
				/>
			</PanelBody>

			<ConditionalTagsOptions
				{...props('conditionalTags', attributes, {
					conditionalTagsBlockName: selectName,
					conditionalTagsIsHidden: checkAttr('selectFieldHidden', attributes, manifest),
				})}
			/>
		</>
	);
};
