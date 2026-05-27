/* global esFormsLocalization */

import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { ContainerPanel, InputField, Toggle, OptionSelect, Tab, TabList, Tabs, TabPanel, Container, ContainerGroup, HStack } from '@eightshift/ui-components';
import { checks, fieldPlaceholder, fieldRequired, googleTagManager, none, order, regex, search, titleGeneric, visible, buttonGhost, design, moreH, sliders, tag, plusCircle, rename } from '@eightshift/ui-components/icons';
import { FieldOptions, FieldOptionsMore, FieldOptionsLayout, FieldOptionsVisibility } from '../../field/components/field-options';
import { isOptionDisabled, NameField } from '../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import manifest from '../manifest.json';
import { HelpTooltip } from '../../../assets/scripts/help-tooltip';

export const PhoneOptions = (attributes) => {
	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const phoneName = checkAttr('phoneName', attributes, manifest);
	const phoneValue = checkAttr('phoneValue', attributes, manifest);
	const phonePlaceholder = checkAttr('phonePlaceholder', attributes, manifest);
	const phoneIsNumber = checkAttr('phoneIsNumber', attributes, manifest); // Used in validation class to validate if the input is a number.
	const phoneIsDisabled = checkAttr('phoneIsDisabled', attributes, manifest);
	const phoneIsRequired = checkAttr('phoneIsRequired', attributes, manifest);
	const phoneTracking = checkAttr('phoneTracking', attributes, manifest);
	const phoneValidationPattern = checkAttr('phoneValidationPattern', attributes, manifest);
	const phoneDisabledOptions = checkAttr('phoneDisabledOptions', attributes, manifest);
	const phoneUseSearch = checkAttr('phoneUseSearch', attributes, manifest);
	const phoneUseLabelAsPlaceholder = checkAttr('phoneUseLabelAsPlaceholder', attributes, manifest);
	const phoneSelectValue = checkAttr('phoneSelectValue', attributes, manifest);
	const phoneValueType = checkAttr('phoneValueType', attributes, manifest);
	const phoneViewType = checkAttr('phoneViewType', attributes, manifest);

	let phoneValidationPatternOptions = [
		{
			label: __('Off', 'eightshift-forms'),
			value: '',
			separator: 'below',
		},
	];

	if (typeof esFormsLocalization !== 'undefined') {
		phoneValidationPatternOptions = [...phoneValidationPatternOptions, ...esFormsLocalization.validationPatternsOptions];
	}

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
						value={phoneName}
						attribute={getAttrKey('phoneName', attributes, manifest)}
						disabledOptions={phoneDisabledOptions}
						setAttributes={setAttributes}
						type='phone'
						isChanged={isNameChanged}
						setIsChanged={setIsNameChanged}
					/>

					<ContainerGroup>
						<Container>
							<InputField
								icon={titleGeneric}
								label={
									<HStack>
										{__('Initial country code', 'eightshift-forms')}

										<HelpTooltip>
											{__('Only one value is allowed.', 'eightshift-forms')}

											<br />
											<br />

											{__("Phone dropdown can't be empty, so if no value is provided the first option will be selected.", 'eightshift-forms')}

											<br />
											<br />

											{__("If geolocation is enabled, it will be preselected based on the user's location.", 'eightshift-forms')}
										</HelpTooltip>
									</HStack>
								}
								placeholder={__('e.g. hr', 'eightshift-forms')}
								value={phoneSelectValue}
								onChange={(value) => setAttributes({ [getAttrKey('phoneSelectValue', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('phoneSelectValue', attributes, manifest), phoneDisabledOptions)}
								className='esf:w-80'
								monospaceFont
								inline
							/>
						</Container>

						<Container>
							<InputField
								icon={rename}
								label={__('Initial number', 'eightshift-forms')}
								type='number'
								min='1'
								value={phoneValue}
								onChange={(value) => setAttributes({ [getAttrKey('phoneValue', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('phoneValue', attributes, manifest), phoneDisabledOptions)}
								placeholder={__('e.g. 123456', 'eightshift-forms')}
								className='esf:w-120'
								monospaceFont
								inline
							/>
						</Container>
					</ContainerGroup>

					<Container standalone>
						<Toggle
							icon={search}
							label={__('Allow searching options', 'eightshift-forms')}
							checked={phoneUseSearch}
							onChange={(value) => setAttributes({ [getAttrKey('phoneUseSearch', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('phoneUseSearch', attributes, manifest), phoneDisabledOptions)}
						/>
					</Container>

					<ContainerGroup>
						<FieldOptionsVisibility
							{...props('field', attributes, {
								fieldDisabledOptions: phoneDisabledOptions,
							})}
						/>

						<Container>
							<Toggle
								icon={none}
								label={__('Disabled', 'eightshift-forms')}
								checked={phoneIsDisabled}
								onChange={(value) => setAttributes({ [getAttrKey('phoneIsDisabled', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('phoneIsDisabled', attributes, manifest), phoneDisabledOptions)}
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<FieldOptions
						{...props('field', attributes, {
							fieldDisabledOptions: phoneDisabledOptions,
						})}
						additionalControls={(hasLabel) => {
							if (!hasLabel || phoneUseLabelAsPlaceholder) {
								return null;
							}

							return (
								<Container>
									<InputField
										actions={<HelpTooltip>{__('Shown when the field is empty', 'eightshift-forms')}</HelpTooltip>}
										icon={fieldPlaceholder}
										label={__('Placeholder', 'eightshift-forms')}
										value={phonePlaceholder}
										onChange={(value) => setAttributes({ [getAttrKey('phonePlaceholder', attributes, manifest)]: value })}
										disabled={isOptionDisabled(getAttrKey('phonePlaceholder', attributes, manifest), phoneDisabledOptions)}
									/>
								</Container>
							);
						}}
						additionalControlsInner={(hasLabel) => {
							if (!hasLabel) {
								return null;
							}

							return (
								<Container>
									<Toggle
										icon={buttonGhost}
										label={__('Show as placeholder', 'eightshift-forms')}
										checked={phoneUseLabelAsPlaceholder}
										onChange={(value) => {
											setAttributes({ [getAttrKey('phonePlaceholder', attributes, manifest)]: undefined });
											setAttributes({ [getAttrKey('phoneUseLabelAsPlaceholder', attributes, manifest)]: value });
										}}
									/>
								</Container>
							);
						}}
					/>

					<FieldOptionsMore
						{...props('field', attributes, {
							fieldDisabledOptions: phoneDisabledOptions,
						})}
					/>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<FieldOptionsLayout
						{...props('field', attributes, {
							fieldDisabledOptions: phoneDisabledOptions,
						})}
					/>

					<Container standalone>
						<OptionSelect
							icon={visible}
							label={__('View type', 'eightshift-forms')}
							help={__('Select the type of view for the phone field.', 'eightshift-forms')}
							options={[
								{
									value: 'number',
									label: __('Number', 'eightshift-forms'),
								},
								{
									value: 'number-country-code',
									label: __('Number with country code', 'eightshift-forms'),
								},
								{
									value: 'number-country-label',
									label: __('Number with country label', 'eightshift-forms'),
								},
							]}
							value={phoneViewType}
							onChange={(value) => setAttributes({ [getAttrKey('phoneViewType', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('phoneViewType', attributes, manifest), phoneDisabledOptions)}
							type='menu'
							inline
						/>
					</Container>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<Container standalone>
						<Toggle
							icon={fieldRequired}
							label={__('Required', 'eightshift-forms')}
							checked={phoneIsRequired}
							onChange={(value) => setAttributes({ [getAttrKey('phoneIsRequired', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('phoneIsRequired', attributes, manifest), phoneDisabledOptions)}
						/>
					</Container>

					<Container standalone>
						<OptionSelect
							icon={regex}
							label={__('Match pattern', 'eightshift-forms')}
							options={phoneValidationPatternOptions}
							value={phoneValidationPattern}
							onChange={(value) => setAttributes({ [getAttrKey('phoneValidationPattern', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('phoneValidationPattern', attributes, manifest), phoneDisabledOptions)}
							type='menu'
							inline
						/>
					</Container>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<ConditionalTagsOptions
						{...props('conditionalTags', attributes, {
							conditionalTagsBlockName: phoneName,
							conditionalTagsIsHidden: checkAttr('phoneFieldHidden', attributes, manifest),
						})}
					/>

					<ContainerGroup>
						<Container>
							<Toggle
								icon={order}
								label={__('Output numbers only', 'eightshift-forms')}
								checked={phoneIsNumber}
								onChange={(value) => setAttributes({ [getAttrKey('phoneIsNumber', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('phoneIsNumber', attributes, manifest), phoneDisabledOptions)}
							/>
						</Container>

						<Container>
							<Toggle
								icon={plusCircle}
								label={__('Include "+" in calling code prefix', 'eightshift-forms')}
								checked={!phoneIsNumber && phoneValueType === 'countryNumberWithPlusPrefix'}
								onChange={(value) =>
									setAttributes({
										[getAttrKey('phoneValueType', attributes, manifest)]: value ? 'countryNumberWithPlusPrefix' : 'countryNumber',
									})
								}
								disabled={phoneIsNumber}
								isIndeterminate={phoneIsNumber}
							/>
						</Container>
					</ContainerGroup>

					<ContainerGroup>
						<Container>
							<InputField
								icon={googleTagManager}
								label={__('GTM tracking code', 'eightshift-forms')}
								placeholder={__('Enter GTM tracking code', 'eightshift-forms')}
								value={phoneTracking}
								onChange={(value) => setAttributes({ [getAttrKey('phoneTracking', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('phoneTracking', attributes, manifest), phoneDisabledOptions)}
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>
		</Tabs>
	);
};
