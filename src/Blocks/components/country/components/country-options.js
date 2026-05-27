import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { NumberPicker, ContainerPanel, InputField, Toggle, ContainerGroup, Tab, TabList, Tabs, TabPanel, Container, OptionSelect } from '@eightshift/ui-components';
import { buttonGhost, checks, chevronLeft, chevronRight, codeVariable, design, fieldPlaceholder, googleTagManager, moreH, multiple, none, requiredAlt, search, sliders, tag, titleGeneric } from '@eightshift/ui-components/icons';
import { FieldOptions, FieldOptionsMore, FieldOptionsLayout, FieldOptionsVisibility } from '../../field/components/field-options';
import { isOptionDisabled, NameField } from '../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import { HelpTooltip } from '../../../assets/scripts/help-tooltip';
import manifest from '../manifest.json';

export const CountryOptions = (attributes) => {
	const { options } = manifest;

	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const countryName = checkAttr('countryName', attributes, manifest);
	const countryIsDisabled = checkAttr('countryIsDisabled', attributes, manifest);
	const countryIsRequired = checkAttr('countryIsRequired', attributes, manifest);
	const countryTracking = checkAttr('countryTracking', attributes, manifest);
	const countryDisabledOptions = checkAttr('countryDisabledOptions', attributes, manifest);
	const countryUseSearch = checkAttr('countryUseSearch', attributes, manifest);
	const countryPlaceholder = checkAttr('countryPlaceholder', attributes, manifest);
	const countryUseLabelAsPlaceholder = checkAttr('countryUseLabelAsPlaceholder', attributes, manifest);
	const countryValueType = checkAttr('countryValueType', attributes, manifest);
	const countryIsMultiple = checkAttr('countryIsMultiple', attributes, manifest);
	const countryMinCount = checkAttr('countryMinCount', attributes, manifest);
	const countryMaxCount = checkAttr('countryMaxCount', attributes, manifest);
	const countryValue = checkAttr('countryValue', attributes, manifest);

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
						value={countryName}
						attribute={getAttrKey('countryName', attributes, manifest)}
						disabledOptions={countryDisabledOptions}
						setAttributes={setAttributes}
						type={'country'}
						isChanged={isNameChanged}
						setIsChanged={setIsNameChanged}
					/>

					<Container standalone>
						<InputField
							icon={titleGeneric}
							label={__('Initial value', 'eightshift-forms')}
							value={countryValue}
							onChange={(value) => setAttributes({ [getAttrKey('countryValue', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('countryValue', attributes, manifest), countryDisabledOptions)}
							placeholder={__('country code, e.g. "hr"', 'eightshift-forms')}
							actions={
								<HelpTooltip>
									{__('Separate multiple country codes with a comma.', 'eightshift-forms')}
									<br />
									<br />
									{__("If geolocation is enabled, it will be preselected based on the user's location.", 'eightshift-forms')}
								</HelpTooltip>
							}
							monospaceFont
						/>
					</Container>

					<ContainerGroup>
						<Container>
							<Toggle
								icon={multiple}
								label={__('Select multiple items', 'eightshift-forms')}
								checked={countryIsMultiple}
								onChange={(value) => {
									setAttributes({ [getAttrKey('countryIsMultiple', attributes, manifest)]: value });
								}}
								disabled={isOptionDisabled(getAttrKey('countryIsMultiple', attributes, manifest), countryDisabledOptions)}
							/>
						</Container>

						<Container>
							<Toggle
								icon={search}
								label={__('Allow searching options', 'eightshift-forms')}
								checked={countryUseSearch}
								onChange={(value) => setAttributes({ [getAttrKey('countryUseSearch', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('countryUseSearch', attributes, manifest), countryDisabledOptions)}
							/>
						</Container>
					</ContainerGroup>

					<ContainerGroup>
						<FieldOptionsVisibility
							{...props('field', attributes, {
								fieldDisabledOptions: countryDisabledOptions,
							})}
						/>

						<Container>
							<Toggle
								icon={none}
								label={__('Disabled', 'eightshift-forms')}
								checked={countryIsDisabled}
								onChange={(value) => setAttributes({ [getAttrKey('countryIsDisabled', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('countryIsDisabled', attributes, manifest), countryDisabledOptions)}
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<FieldOptions
						{...props('field', attributes, {
							fieldDisabledOptions: countryDisabledOptions,
						})}
						additionalControls={(hasLabel) => {
							if (!hasLabel || countryUseLabelAsPlaceholder) {
								return null;
							}

							return (
								<Container>
									<InputField
										actions={<HelpTooltip>{__('Shown when the field is empty', 'eightshift-forms')}</HelpTooltip>}
										icon={fieldPlaceholder}
										label={__('Placeholder', 'eightshift-forms')}
										value={countryPlaceholder}
										onChange={(value) => setAttributes({ [getAttrKey('countryPlaceholder', attributes, manifest)]: value })}
										disabled={isOptionDisabled(getAttrKey('countryPlaceholder', attributes, manifest), countryDisabledOptions)}
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
										checked={countryUseLabelAsPlaceholder}
										onChange={(value) => {
											setAttributes({ [getAttrKey('countryPlaceholder', attributes, manifest)]: undefined });
											setAttributes({ [getAttrKey('countryUseLabelAsPlaceholder', attributes, manifest)]: value });
										}}
									/>
								</Container>
							);
						}}
					/>

					<FieldOptionsMore
						{...props('field', attributes, {
							fieldDisabledOptions: countryDisabledOptions,
						})}
					/>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<FieldOptionsLayout
						{...props('field', attributes, {
							fieldDisabledOptions: countryDisabledOptions,
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
							checked={countryIsRequired}
							onChange={(value) => setAttributes({ [getAttrKey('countryIsRequired', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('countryIsRequired', attributes, manifest), countryDisabledOptions)}
						/>
					</Container>

					<ContainerGroup hidden={!countryIsMultiple}>
						<Container>
							<NumberPicker
								icon={chevronRight}
								label={__('Min. items', 'eightshift-forms')}
								value={countryMinCount}
								onChange={(value) => setAttributes({ [getAttrKey('countryMinCount', attributes, manifest)]: value })}
								min={options.countryMinCount.min}
								step={options.countryMinCount.step}
								disabled={isOptionDisabled(getAttrKey('countryMinCount', attributes, manifest), countryDisabledOptions)}
								inline
							/>
						</Container>

						<Container>
							<NumberPicker
								icon={chevronLeft}
								label={__('Max. items', 'eightshift-forms')}
								value={countryMaxCount}
								onChange={(value) => setAttributes({ [getAttrKey('countryMaxCount', attributes, manifest)]: value })}
								min={options.countryMaxCount.min}
								step={options.countryMaxCount.step}
								disabled={isOptionDisabled(getAttrKey('countryMaxCount', attributes, manifest), countryDisabledOptions)}
								inline
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<Container standalone>
						<OptionSelect
							icon={codeVariable}
							value={countryValueType}
							onChange={(value) => setAttributes({ [getAttrKey('countryValueType', attributes, manifest)]: value })}
							label={__('Output value', 'eightshift-forms')}
							options={[
								{
									value: 'countryCode',
									label: __('Country code', 'eightshift-forms'),
									subtitle: __('Lowercase', 'eightshift-forms'),
									endIcon: <span className='es:font-mono'>hr</span>,
								},
								{
									value: 'countryCodeUppercase',
									label: __('Country code', 'eightshift-forms'),
									subtitle: __('Uppercase', 'eightshift-forms'),
									endIcon: <span className='es:font-mono'>HR</span>,
								},
								{
									value: 'countryName',
									label: __('Local country name', 'eightshift-forms'),
									endIcon: <span className='es:font-mono'>{__('Hrvatska', 'eightshift-forms')}</span>,
								},
								{
									value: 'countryUnlocalizedName',
									label: __('English country name', 'eightshift-forms'),
									endIcon: <span className='es:font-mono'>Croatia</span>,
								},
							]}
							type='menu'
							inline
						/>
					</Container>

					<ConditionalTagsOptions
						{...props('conditionalTags', attributes, {
							conditionalTagsBlockName: countryName,
							conditionalTagsIsHidden: checkAttr('countryFieldHidden', attributes, manifest),
						})}
					/>

					<ContainerGroup label={__('Tracking', 'eighshift-forms')}>
						<Container>
							<InputField
								icon={googleTagManager}
								label={__('GTM tracking code', 'eightshift-forms')}
								placeholder={__('Enter GTM tracking code', 'eightshift-forms')}
								value={countryTracking}
								onChange={(value) => setAttributes({ [getAttrKey('countryTracking', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('countryTracking', attributes, manifest), countryDisabledOptions)}
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>
		</Tabs>
	);
};
