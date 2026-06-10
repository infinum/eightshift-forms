/* global esFormsLocalization */

import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { buttonGhost, checks, design, fieldPlaceholder, googleTagManager, moreH, none, regex, sliders, tag, titleGeneric, requiredAlt, chevronRight, chevronLeft } from '@eightshift/ui-components/icons';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { NumberPicker, InputField, Toggle, ContainerPanel, Tab, TabList, Tabs, TabPanel, Container, ContainerGroup, OptionSelect } from '@eightshift/ui-components';
import { FieldOptions, FieldOptionsMore, FieldOptionsLayout, FieldOptionsVisibility } from '../../field/components/field-options';
import { isOptionDisabled, NameField } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import manifest from '../manifest.json';
import { HelpTooltip } from '../../../assets/scripts/help-tooltip';

export const TextareaOptions = (attributes) => {
	const { options } = manifest;

	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const textareaName = checkAttr('textareaName', attributes, manifest);
	const textareaValue = checkAttr('textareaValue', attributes, manifest);
	const textareaPlaceholder = checkAttr('textareaPlaceholder', attributes, manifest);
	const textareaIsDisabled = checkAttr('textareaIsDisabled', attributes, manifest);
	const textareaIsRequired = checkAttr('textareaIsRequired', attributes, manifest);
	const textareaTracking = checkAttr('textareaTracking', attributes, manifest);
	const textareaValidationPattern = checkAttr('textareaValidationPattern', attributes, manifest);
	const textareaDisabledOptions = checkAttr('textareaDisabledOptions', attributes, manifest);
	const textareaMinLength = checkAttr('textareaMinLength', attributes, manifest);
	const textareaMaxLength = checkAttr('textareaMaxLength', attributes, manifest);
	const textareaUseLabelAsPlaceholder = checkAttr('textareaUseLabelAsPlaceholder', attributes, manifest);

	let textareaValidationPatternOptions = [];

	textareaValidationPatternOptions = [
		{
			label: __('Off', 'eightshift-forms'),
			value: '',
			separator: 'below',
		},
	];

	if (typeof esFormsLocalization !== 'undefined') {
		textareaValidationPatternOptions = esFormsLocalization.validationPatternsOptions;
		textareaValidationPatternOptions = [
			{
				label: __('Off', 'eightshift-forms'),
				value: '',
				separator: 'below',
			},
			...textareaValidationPatternOptions,
		];
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
						value={textareaName}
						attribute={getAttrKey('textareaName', attributes, manifest)}
						disabledOptions={textareaDisabledOptions}
						setAttributes={setAttributes}
						type='textarea'
						isChanged={isNameChanged}
						setIsChanged={setIsNameChanged}
					/>

					<Container standalone>
						<InputField
							icon={titleGeneric}
							label={__('Initial value', 'eightshift-forms')}
							value={textareaValue}
							onChange={(value) => setAttributes({ [getAttrKey('textareaValue', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('textareaValue', attributes, manifest), textareaDisabledOptions)}
							inline
						/>
					</Container>

					<ContainerGroup>
						<FieldOptionsVisibility
							{...props('field', attributes, {
								fieldDisabledOptions: textareaDisabledOptions,
							})}
						/>

						<Container>
							<Toggle
								icon={none}
								label={__('Disabled', 'eightshift-forms')}
								checked={textareaIsDisabled}
								onChange={(value) => setAttributes({ [getAttrKey('textareaIsDisabled', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('textareaIsDisabled', attributes, manifest), textareaDisabledOptions)}
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<FieldOptions
						{...props('field', attributes, {
							fieldDisabledOptions: textareaDisabledOptions,
						})}
						additionalControls={(hasLabel) => {
							if (!hasLabel || textareaUseLabelAsPlaceholder) {
								return null;
							}

							return (
								<Container>
									<InputField
										actions={<HelpTooltip>{__('Shown when the field is empty', 'eightshift-forms')}</HelpTooltip>}
										icon={fieldPlaceholder}
										label={__('Placeholder', 'eightshift-forms')}
										value={textareaPlaceholder}
										onChange={(value) => setAttributes({ [getAttrKey('textareaPlaceholder', attributes, manifest)]: value })}
										disabled={isOptionDisabled(getAttrKey('textareaPlaceholder', attributes, manifest), textareaDisabledOptions)}
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
										checked={textareaUseLabelAsPlaceholder}
										onChange={(value) => {
											setAttributes({ [getAttrKey('textareaPlaceholder', attributes, manifest)]: undefined });
											setAttributes({ [getAttrKey('textareaUseLabelAsPlaceholder', attributes, manifest)]: value });
										}}
									/>
								</Container>
							);
						}}
					/>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<FieldOptionsLayout
						{...props('field', attributes, {
							fieldDisabledOptions: textareaDisabledOptions,
						})}
					/>

					<FieldOptionsMore
						{...props('field', attributes, {
							fieldDisabledOptions: textareaDisabledOptions,
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
							checked={textareaIsRequired}
							onChange={(value) => setAttributes({ [getAttrKey('textareaIsRequired', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('textareaIsRequired', attributes, manifest), textareaDisabledOptions)}
						/>
					</Container>

					<Container standalone>
						<OptionSelect
							icon={regex}
							label={__('Match pattern', 'eightshift-forms')}
							options={textareaValidationPatternOptions}
							value={textareaValidationPattern}
							onChange={(value) => setAttributes({ [getAttrKey('textareaValidationPattern', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('textareaValidationPattern', attributes, manifest), textareaDisabledOptions)}
							type='menu'
							inline
						/>
					</Container>

					<ContainerGroup>
						<Container>
							<NumberPicker
								icon={chevronRight}
								label={__('Min. length', 'eightshift-forms')}
								value={textareaMinLength}
								onChange={(value) => setAttributes({ [getAttrKey('textareaMinLength', attributes, manifest)]: value })}
								min={options.textareaMinLength.min}
								max={options.textareaMinLength.max}
								step={options.textareaMinLength.step}
								disabled={isOptionDisabled(getAttrKey('textareaMinLength', attributes, manifest), textareaDisabledOptions)}
								inline
							/>
						</Container>

						<Container>
							<NumberPicker
								icon={chevronLeft}
								label={__('Max. length', 'eightshift-forms')}
								value={textareaMaxLength}
								onChange={(value) => setAttributes({ [getAttrKey('textareaMaxLength', attributes, manifest)]: value })}
								min={options.textareaMaxLength.min}
								max={options.textareaMaxLength.max}
								step={options.textareaMaxLength.step}
								disabled={isOptionDisabled(getAttrKey('textareaMaxLength', attributes, manifest), textareaDisabledOptions)}
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
							conditionalTagsBlockName: textareaName,
							conditionalTagsIsHidden: checkAttr('textareaFieldHidden', attributes, manifest),
						})}
					/>

					<ContainerGroup label={__('Tracking', 'eightshift-forms')}>
						<Container>
							<InputField
								icon={googleTagManager}
								label={__('GTM tracking code', 'eightshift-forms')}
								value={textareaTracking}
								onChange={(value) => setAttributes({ [getAttrKey('textareaTracking', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('textareaTracking', attributes, manifest), textareaDisabledOptions)}
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>
		</Tabs>
	);
};
