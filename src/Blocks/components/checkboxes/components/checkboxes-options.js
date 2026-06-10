import { useEffect } from 'react';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { ContainerPanel, InputField, Toggle, Tab, TabList, Tabs, TabPanel, Container, ContainerGroup, OptionSelect } from '@eightshift/ui-components';
import { FieldOptions, FieldOptionsMore, FieldOptionsLayout, FieldOptionsVisibility } from '../../field/components/field-options';
import { buttonGhost, checks, design, fieldPlaceholder, moreH, optionListAlt, requiredAlt, sliders, tag } from '@eightshift/ui-components/icons';
import { isOptionDisabled, NameField } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import manifest from '../manifest.json';
import globalManifest from '../../../manifest.json';
import { NumberPicker } from '@eightshift/ui-components';
import { HelpTooltip } from '../../../assets/scripts/help-tooltip';

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
		<Tabs>
			<TabList>
				<Tab
					icon={sliders}
					label={__('General', 'eightshift-forms')}
				/>

				<Tab
					icon={tag}
					label={__('Labels', 'eightshift-forms')}
				/>

				<Tab
					icon={design}
					label={__('Design', 'eightshift-forms')}
				/>

				<Tab
					icon={checks}
					label={__('Validation', 'eightshift-forms')}
				/>

				<Tab
					icon={moreH}
					label={__('Advanced', 'eightshift-forms')}
				/>
			</TabList>

			<TabPanel>
				<ContainerPanel>
					<NameField
						value={checkboxesName}
						attribute={getAttrKey('checkboxesName', attributes, manifest)}
						disabledOptions={checkboxesDisabledOptions}
						setAttributes={setAttributes}
						type='checkboxes'
						isChanged={isNameChanged}
						setIsChanged={setIsNameChanged}
					/>

					<Container standalone>
						<OptionSelect
							icon={optionListAlt}
							label={__('Show as', 'eightshift-forms')}
							value={checkboxesShowAs}
							options={globalManifest.showAsMap.options.map((item) => (item.value === 'checkboxes' ? { ...item, value: '' } : item))}
							disabled={isOptionDisabled(getAttrKey('checkboxesShowAs', attributes, manifest), checkboxesDisabledOptions)}
							onChange={(value) => setAttributes({ [getAttrKey('checkboxesShowAs', attributes, manifest)]: value })}
							type='menu'
							inline
						/>
					</Container>

					<FieldOptionsVisibility
						{...props('field', attributes, {
							fieldDisabledOptions: checkboxesDisabledOptions,
						})}
					/>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<FieldOptions
						{...props('field', attributes, {
							fieldDisabledOptions: checkboxesDisabledOptions,
						})}
						additionalControls={(hasLabel) => {
							if (!hasLabel || checkboxesShowAs !== 'select' || checkboxesUseLabelAsPlaceholder) {
								return null;
							}

							return (
								<Container>
									<InputField
										actions={<HelpTooltip>{__('Shown when the field is empty', 'eightshift-forms')}</HelpTooltip>}
										icon={fieldPlaceholder}
										label={__('Placeholder', 'eightshift-forms')}
										value={checkboxesPlaceholder}
										onChange={(value) => setAttributes({ [getAttrKey('checkboxesPlaceholder', attributes, manifest)]: value })}
										disabled={isOptionDisabled(getAttrKey('checkboxesPlaceholder', attributes, manifest), checkboxesDisabledOptions)}
									/>
								</Container>
							);
						}}
						additionalControlsInner={(hasLabel) => {
							if (!hasLabel || checkboxesShowAs !== 'select') {
								return null;
							}

							return (
								<Container>
									<Toggle
										icon={buttonGhost}
										label={__('Show as placeholder', 'eightshift-forms')}
										checked={checkboxesUseLabelAsPlaceholder}
										onChange={(value) => {
											setAttributes({ [getAttrKey('checkboxesPlaceholder', attributes, manifest)]: undefined });
											setAttributes({ [getAttrKey('checkboxesUseLabelAsPlaceholder', attributes, manifest)]: value });
										}}
									/>
								</Container>
							);
						}}
					/>

					{checkboxesShowAs === 'select' && (
						<>
							{!checkboxesUseLabelAsPlaceholder && (
								<InputField
									help={__('Shown when the field is empty', 'eightshift-forms')}
									value={checkboxesPlaceholder}
									onChange={(value) => setAttributes({ [getAttrKey('checkboxesPlaceholder', attributes, manifest)]: value })}
									disabled={isOptionDisabled(getAttrKey('checkboxesPlaceholder', attributes, manifest), checkboxesDisabledOptions)}
								/>
							)}

							<Toggle
								icon={fieldPlaceholder}
								label={__('Use label as a placeholder', 'eightshift-forms')}
								checked={checkboxesUseLabelAsPlaceholder}
								onChange={(value) => {
									setAttributes({ [getAttrKey('checkboxesPlaceholder', attributes, manifest)]: undefined });
									setAttributes({ [getAttrKey('checkboxesUseLabelAsPlaceholder', attributes, manifest)]: value });
								}}
							/>
						</>
					)}

					<FieldOptionsMore
						{...props('field', attributes, {
							fieldDisabledOptions: checkboxesDisabledOptions,
						})}
					/>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<FieldOptionsLayout
						{...props('field', attributes, {
							fieldDisabledOptions: checkboxesDisabledOptions,
						})}
					/>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<ContainerGroup>
						<Container>
							<Toggle
								icon={requiredAlt}
								label={__('Required', 'eightshift-forms')}
								checked={checkboxesIsRequired}
								onChange={(value) => {
									setAttributes({ [getAttrKey('checkboxesIsRequired', attributes, manifest)]: value });

									if (!value) {
										setAttributes({ [getAttrKey('checkboxesIsRequiredCount', attributes, manifest)]: 1 });
									}
								}}
							/>
						</Container>

						<Container hidden={!checkboxesIsRequired || checkboxesShowAs === 'radio'}>
							<NumberPicker
								icon={optionListAlt}
								label={__('Minimum selections', 'eightshift-forms')}
								value={checkboxesIsRequiredCount}
								onChange={(value) => setAttributes({ [getAttrKey('checkboxesIsRequiredCount', attributes, manifest)]: value })}
								min={options.checkboxesIsRequiredCount.min}
								max={countInnerBlocks}
								disabled={isOptionDisabled(getAttrKey('checkboxesIsRequiredCount', attributes, manifest), checkboxesDisabledOptions)}
								inline
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<ConditionalTagsOptions
						{...props('conditionalTags', attributes, {
							conditionalTagsBlockName: checkboxesName,
							conditionalTagsIsHidden: checkAttr('checkboxesFieldHidden', attributes, manifest),
						})}
					/>
				</ContainerPanel>
			</TabPanel>
		</Tabs>
	);
};
