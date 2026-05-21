/* global esFormsLocalization */

import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey, props, getOption } from '@eightshift/frontend-libs-tailwind/scripts';
import {
	FieldOptions,
	FieldOptionsMore,
	FieldOptionsLayout,
	FieldOptionsVisibility,
} from '../../field/components/field-options';
import {
	ContainerPanel,
	InputField,
	Toggle,
	ContainerGroup,
	Tab,
	TabList,
	Tabs,
	TabPanel,
	Container,
	OptionSelect,
	BaseControl,
} from '@eightshift/ui-components';
import {
	checks,
	fieldPlaceholder,
	fieldValue,
	googleTagManager,
	options,
	regex,
	buttonGhost,
	design,
	moreH,
	requiredAlt,
	sliders,
	tag,
	itemSelect,
	none,
	visible,
	codeVariable,
	externalLink,
} from '@eightshift/ui-components/icons';
import { isOptionDisabled, NameField } from '../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import manifest from '../manifest.json';
import { HelpTooltip } from '../../../assets/scripts/help-tooltip';

export const DateOptions = (attributes) => {
	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const dateName = checkAttr('dateName', attributes, manifest);
	const dateValue = checkAttr('dateValue', attributes, manifest);
	const datePlaceholder = checkAttr('datePlaceholder', attributes, manifest);
	const dateIsDisabled = checkAttr('dateIsDisabled', attributes, manifest);
	const dateIsRequired = checkAttr('dateIsRequired', attributes, manifest);
	const dateTracking = checkAttr('dateTracking', attributes, manifest);
	const dateValidationPattern = checkAttr('dateValidationPattern', attributes, manifest);
	const dateDisabledOptions = checkAttr('dateDisabledOptions', attributes, manifest);
	const dateType = checkAttr('dateType', attributes, manifest);
	const dateUseLabelAsPlaceholder = checkAttr('dateUseLabelAsPlaceholder', attributes, manifest);
	const datePreviewFormat = checkAttr('datePreviewFormat', attributes, manifest);
	const dateOutputFormat = checkAttr('dateOutputFormat', attributes, manifest);
	const dateMode = checkAttr('dateMode', attributes, manifest);

	let dateValidationPatternOptions = [
		{
			label: __('Off', 'eightshift-forms'),
			value: '',
			separator: 'below',
		},
	];

	if (typeof esFormsLocalization !== 'undefined') {
		dateValidationPatternOptions = [...dateValidationPatternOptions, ...esFormsLocalization.validationPatternsOptions];
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
						value={dateName}
						attribute={getAttrKey('dateName', attributes, manifest)}
						disabledOptions={dateDisabledOptions}
						setAttributes={setAttributes}
						type='date'
						isChanged={isNameChanged}
						setIsChanged={setIsNameChanged}
					/>

					<ContainerGroup>
						<Container>
							<OptionSelect
								icon={options}
								label={__('Mode', 'eightshift-forms')}
								value={dateType}
								options={getOption('dateType', attributes, manifest)}
								disabled={isOptionDisabled(getAttrKey('dateType', attributes, manifest), dateDisabledOptions)}
								onChange={(value) => setAttributes({ [getAttrKey('dateType', attributes, manifest)]: value })}
								inline
							/>
						</Container>

						<Container>
							<OptionSelect
								icon={itemSelect}
								label={__('Selection', 'eightshift-forms')}
								value={dateMode}
								options={getOption('dateMode', attributes, manifest)}
								disabled={isOptionDisabled(getAttrKey('dateMode', attributes, manifest), dateDisabledOptions)}
								onChange={(value) => setAttributes({ [getAttrKey('dateMode', attributes, manifest)]: value })}
								inline
							/>
						</Container>
					</ContainerGroup>

					<Container standalone>
						<InputField
							icon={fieldValue}
							label={__('Initial value', 'eightshift-forms')}
							placeholder={
								dateType === 'date'
									? __('e.g. 2026-05-20', 'eightshift-forms')
									: __('e.g. 2026-05-20 14:30', 'eightshift-forms')
							}
							value={dateValue}
							onChange={(value) => setAttributes({ [getAttrKey('dateValue', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('dateValue', attributes, manifest), dateDisabledOptions)}
							monospaceFont
							inline
						/>
					</Container>

					<ContainerGroup>
						<FieldOptionsVisibility
							{...props('field', attributes, {
								fieldDisabledOptions: dateDisabledOptions,
							})}
						/>

						<Container>
							<Toggle
								icon={none}
								label={__('Disabled', 'eightshift-forms')}
								checked={dateIsDisabled}
								onChange={(value) => setAttributes({ [getAttrKey('dateIsDisabled', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('dateIsDisabled', attributes, manifest), dateDisabledOptions)}
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<FieldOptions
						{...props('field', attributes, {
							fieldDisabledOptions: dateDisabledOptions,
						})}
						additionalControls={(hasLabel) => {
							if (!hasLabel || dateUseLabelAsPlaceholder) {
								return null;
							}

							return (
								<Container>
									<InputField
										actions={<HelpTooltip>{__('Shown when the field is empty', 'eightshift-forms')}</HelpTooltip>}
										icon={fieldPlaceholder}
										label={__('Placeholder', 'eightshift-forms')}
										value={datePlaceholder}
										onChange={(value) =>
											setAttributes({ [getAttrKey('datePlaceholder', attributes, manifest)]: value })
										}
										disabled={isOptionDisabled(
											getAttrKey('datePlaceholder', attributes, manifest),
											dateDisabledOptions,
										)}
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
										checked={dateUseLabelAsPlaceholder}
										onChange={(value) => {
											setAttributes({ [getAttrKey('datePlaceholder', attributes, manifest)]: undefined });
											setAttributes({ [getAttrKey('dateUseLabelAsPlaceholder', attributes, manifest)]: value });
										}}
									/>
								</Container>
							);
						}}
					/>

					<FieldOptionsMore
						{...props('field', attributes, {
							fieldDisabledOptions: dateDisabledOptions,
						})}
					/>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<FieldOptionsLayout
						{...props('field', attributes, {
							fieldDisabledOptions: dateDisabledOptions,
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
							checked={dateIsRequired}
							onChange={(value) => setAttributes({ [getAttrKey('dateIsRequired', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('dateIsRequired', attributes, manifest), dateDisabledOptions)}
						/>
					</Container>

					<Container standalone>
						<OptionSelect
							icon={regex}
							label={__('Match pattern', 'eightshift-forms')}
							options={dateValidationPatternOptions}
							value={dateValidationPattern}
							onChange={(value) =>
								setAttributes({ [getAttrKey('dateValidationPattern', attributes, manifest)]: value })
							}
							disabled={isOptionDisabled(
								getAttrKey('dateValidationPattern', attributes, manifest),
								dateDisabledOptions,
							)}
							type='menu'
							inline
						/>
					</Container>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<ContainerGroup label={__('Formats', 'eightshift-forms')}>
						<Container>
							<InputField
								label={__('Display', 'eightshift-forms')}
								icon={visible}
								value={datePreviewFormat}
								placeholder={manifest.formats[dateType].preview}
								onChange={(value) => setAttributes({ [getAttrKey('datePreviewFormat', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('datePreviewFormat', attributes, manifest), dateDisabledOptions)}
								monospaceFont
								inline
							/>
						</Container>

						<Container>
							<InputField
								icon={codeVariable}
								label={__('Output', 'eightshift-forms')}
								value={dateOutputFormat}
								placeholder={manifest.formats[dateType].output}
								onChange={(value) => setAttributes({ [getAttrKey('dateOutputFormat', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('dateOutputFormat', attributes, manifest), dateDisabledOptions)}
								monospaceFont
								inline
							/>
						</Container>

						<Container compact>
							<BaseControl
								label={__('More about date formats', 'eightshift-forms')}
								controlContainerClassName='esf:min-h-28'
								inline
							>
								<a
									href='https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date#date_time_string_format'
									target='_blank'
									className='esf:flex esf:items-center esf:gap-4 esf:[&_svg]:size-16 esf:[&_svg]:stroke-[1.5]'
								>
									{externalLink}
									{__('Docs', 'eightshift-forms')}
								</a>
							</BaseControl>
						</Container>
					</ContainerGroup>

					<ConditionalTagsOptions
						{...props('conditionalTags', attributes, {
							conditionalTagsBlockName: dateName,
							conditionalTagsIsHidden: checkAttr('dateFieldHidden', attributes, manifest),
						})}
					/>

					<ContainerGroup label={__('Tracking', 'eightshift-forms')}>
						<Container>
							<InputField
								icon={googleTagManager}
								label={__('GTM tracking code', 'eightshift-forms')}
								value={dateTracking}
								onChange={(value) => setAttributes({ [getAttrKey('dateTracking', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('dateTracking', attributes, manifest), dateDisabledOptions)}
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>
		</Tabs>
	);
};
