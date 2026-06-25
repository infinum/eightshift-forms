import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { checks, fieldPlaceholder, googleTagManager, optionListAlt, search, design, moreH, requiredAlt, sliders, tag, none, optionList, chevronRight, chevronLeft, buttonGhost } from '@eightshift/ui-components/icons';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { NumberPicker, ContainerPanel, InputField, Toggle, Tab, TabList, Tabs, TabPanel, Container, ContainerGroup, OptionSelect } from '@eightshift/ui-components';
import { FieldOptions, FieldOptionsMore, FieldOptionsLayout, FieldOptionsVisibility } from '../../field/components/field-options';
import { isOptionDisabled, NameField } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import { HelpTooltip } from '../../../assets/scripts/help-tooltip';
import manifest from '../manifest.json';
import globalManifest from '../../../manifest.json';

export const SelectOptions = (attributes) => {
	const { options } = manifest;

	const { setAttributes } = attributes;

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
						value={selectName}
						attribute={getAttrKey('selectName', attributes, manifest)}
						disabledOptions={selectDisabledOptions}
						setAttributes={setAttributes}
						type='select'
						isChanged={isNameChanged}
						setIsChanged={setIsNameChanged}
					/>

					<Container standalone>
						<OptionSelect
							icon={optionListAlt}
							label={__('Show as', 'eightshift-forms')}
							value={selectShowAs}
							options={globalManifest.showAsMap.options.map((item) => (item.value === 'select' ? { ...item, value: '' } : item))}
							disabled={isOptionDisabled(getAttrKey('selectShowAs', attributes, manifest), selectDisabledOptions)}
							onChange={(value) => setAttributes({ [getAttrKey('selectShowAs', attributes, manifest)]: value })}
							type='menu'
							inline
						/>
					</Container>

					<ContainerGroup>
						<Container>
							<Toggle
								icon={optionList}
								label={__('Allow selecting multiple items', 'eightshift-forms')}
								checked={selectIsMultiple}
								onChange={(value) => {
									setAttributes({ [getAttrKey('selectIsMultiple', attributes, manifest)]: value });
									setAttributes({ [getAttrKey('selectMaxCount', attributes, manifest)]: undefined });
									setAttributes({ [getAttrKey('selectMinCount', attributes, manifest)]: undefined });
								}}
								disabled={isOptionDisabled(getAttrKey('selectIsMultiple', attributes, manifest), selectDisabledOptions)}
							/>
						</Container>

						<Container>
							<Toggle
								icon={search}
								label={__('Allow searching options', 'eightshift-forms')}
								checked={selectUseSearch}
								onChange={(value) => setAttributes({ [getAttrKey('selectUseSearch', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('selectUseSearch', attributes, manifest), selectDisabledOptions)}
							/>
						</Container>
					</ContainerGroup>

					<ContainerGroup>
						<FieldOptionsVisibility
							{...props('field', attributes, {
								fieldDisabledOptions: selectDisabledOptions,
							})}
						/>

						<Container>
							<Toggle
								icon={none}
								label={__('Disabled', 'eightshift-forms')}
								checked={selectIsDisabled}
								onChange={(value) => setAttributes({ [getAttrKey('selectIsDisabled', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('selectIsDisabled', attributes, manifest), selectDisabledOptions)}
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<FieldOptions
						{...props('field', attributes, {
							fieldDisabledOptions: selectDisabledOptions,
						})}
						additionalControls={(hasLabel) => {
							if (!hasLabel || selectShowAs !== '' || selectUseLabelAsPlaceholder) {
								return null;
							}

							return (
								<Container>
									<InputField
										actions={<HelpTooltip>{__('Shown when the field is empty', 'eightshift-forms')}</HelpTooltip>}
										icon={fieldPlaceholder}
										label={__('Placeholder', 'eightshift-forms')}
										value={selectPlaceholder}
										onChange={(value) => setAttributes({ [getAttrKey('selectPlaceholder', attributes, manifest)]: value })}
										disabled={isOptionDisabled(getAttrKey('selectPlaceholder', attributes, manifest), selectDisabledOptions)}
									/>
								</Container>
							);
						}}
						additionalControlsInner={(hasLabel) => {
							if (!hasLabel || selectShowAs !== '') {
								return null;
							}

							return (
								<Container>
									<Toggle
										icon={buttonGhost}
										label={__('Show as placeholder', 'eightshift-forms')}
										checked={selectUseLabelAsPlaceholder}
										onChange={(value) => {
											setAttributes({ [getAttrKey('selectPlaceholder', attributes, manifest)]: undefined });
											setAttributes({ [getAttrKey('selectUseLabelAsPlaceholder', attributes, manifest)]: value });
										}}
									/>
								</Container>
							);
						}}
					/>

					<FieldOptionsMore
						{...props('field', attributes, {
							fieldDisabledOptions: selectDisabledOptions,
						})}
					/>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<FieldOptionsLayout
						{...props('field', attributes, {
							fieldDisabledOptions: selectDisabledOptions,
						})}
					/>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<Container standalone>
						<Toggle
							icon={requiredAlt}
							label={__('Required', 'eightshift-forms')}
							checked={selectIsRequired}
							onChange={(value) => setAttributes({ [getAttrKey('selectIsRequired', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('selectIsRequired', attributes, manifest), selectDisabledOptions)}
						/>
					</Container>

					<ContainerGroup hidden={!selectIsMultiple}>
						<Container>
							<NumberPicker
								icon={chevronRight}
								label={__('Min. selected options', 'eightshift-forms')}
								value={selectMinCount}
								onChange={(value) => setAttributes({ [getAttrKey('selectMinCount', attributes, manifest)]: value })}
								min={options.selectMinCount.min}
								step={options.selectMinCount.step}
								disabled={isOptionDisabled(getAttrKey('selectMinCount', attributes, manifest), selectDisabledOptions)}
								fixedWidth={4}
								inline
							/>
						</Container>

						<Container>
							<NumberPicker
								icon={chevronLeft}
								label={__('Max. selected options', 'eightshift-forms')}
								value={selectMaxCount}
								onChange={(value) => setAttributes({ [getAttrKey('selectMaxCount', attributes, manifest)]: value })}
								min={options.selectMaxCount.min}
								step={options.selectMaxCount.step}
								disabled={isOptionDisabled(getAttrKey('selectMaxCount', attributes, manifest), selectDisabledOptions)}
								fixedWidth={4}
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
							conditionalTagsBlockName: selectName,
							conditionalTagsIsHidden: checkAttr('selectFieldHidden', attributes, manifest),
						})}
					/>

					<ContainerGroup label={__('Tracking', 'eightshift-forms')}>
						<Container>
							<InputField
								icon={googleTagManager}
								label={__('GTM tracking code', 'eightshift-forms')}
								value={selectTracking}
								onChange={(value) => setAttributes({ [getAttrKey('selectTracking', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('selectTracking', attributes, manifest), selectDisabledOptions)}
								monospaceFont
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>
		</Tabs>
	);
};
