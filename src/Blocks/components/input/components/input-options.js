/* global esFormsLocalization */

import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { getOption, checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import {
	NumberPicker,
	ContainerPanel,
	InputField,
	Toggle,
	Tabs,
	TabList,
	Tab,
	TabPanel,
	OptionSelect,
	Container,
	ContainerGroup,
} from '@eightshift/ui-components';
import {
	buttonGhost,
	checks,
	chevronLeft,
	chevronRight,
	design,
	fieldPlaceholder,
	fieldValue,
	googleTagManager,
	moreH,
	none,
	positionHEnd,
	positionHStart,
	rangeMax,
	rangeMid,
	rangeMin,
	regex,
	rename,
	requiredAlt,
	sliders,
	step,
	tag,
	titleGeneric,
} from '@eightshift/ui-components/icons';
import {
	FieldOptions,
	FieldOptionsMore,
	FieldOptionsLayout,
	FieldOptionsVisibility,
} from '../../field/components/field-options';
import { isOptionDisabled, NameField } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import manifest from '../manifest.json';
import { HelpTooltip } from '../../../assets/scripts/help-tooltip';

export const InputOptions = (attributes) => {
	const { options } = manifest;

	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const inputName = checkAttr('inputName', attributes, manifest);
	const inputValue = checkAttr('inputValue', attributes, manifest);
	const inputPlaceholder = checkAttr('inputPlaceholder', attributes, manifest);
	const inputType = checkAttr('inputType', attributes, manifest);
	const inputIsDisabled = checkAttr('inputIsDisabled', attributes, manifest);
	const inputIsRequired = checkAttr('inputIsRequired', attributes, manifest);
	const inputTracking = checkAttr('inputTracking', attributes, manifest);
	const inputIsEmail = checkAttr('inputIsEmail', attributes, manifest);
	const inputIsUrl = checkAttr('inputIsUrl', attributes, manifest);
	const inputValidationPattern = checkAttr('inputValidationPattern', attributes, manifest);
	const inputMinLength = checkAttr('inputMinLength', attributes, manifest);
	const inputMaxLength = checkAttr('inputMaxLength', attributes, manifest);
	const inputMin = checkAttr('inputMin', attributes, manifest);
	const inputMax = checkAttr('inputMax', attributes, manifest);
	const inputStep = checkAttr('inputStep', attributes, manifest);
	const inputDisabledOptions = checkAttr('inputDisabledOptions', attributes, manifest);
	const inputUseLabelAsPlaceholder = checkAttr('inputUseLabelAsPlaceholder', attributes, manifest);
	const inputRangeShowMin = checkAttr('inputRangeShowMin', attributes, manifest);
	const inputRangeShowMinPrefix = checkAttr('inputRangeShowMinPrefix', attributes, manifest);
	const inputRangeShowMinSuffix = checkAttr('inputRangeShowMinSuffix', attributes, manifest);
	const inputRangeShowMax = checkAttr('inputRangeShowMax', attributes, manifest);
	const inputRangeShowMaxPrefix = checkAttr('inputRangeShowMaxPrefix', attributes, manifest);
	const inputRangeShowMaxSuffix = checkAttr('inputRangeShowMaxSuffix', attributes, manifest);
	const inputRangeShowCurrent = checkAttr('inputRangeShowCurrent', attributes, manifest);
	const inputRangeShowCurrentPrefix = checkAttr('inputRangeShowCurrentPrefix', attributes, manifest);
	const inputRangeShowCurrentSuffix = checkAttr('inputRangeShowCurrentSuffix', attributes, manifest);
	const inputRangeUseCustomField = checkAttr('inputRangeUseCustomField', attributes, manifest);

	let inputValidationPatternOptions = [
		{
			label: __('Off', 'eightshift-forms'),
			value: '',
			separator: 'below',
		},
	];

	if (typeof esFormsLocalization !== 'undefined') {
		inputValidationPatternOptions = [
			...inputValidationPatternOptions,
			...esFormsLocalization.validationPatternsOptions,
		];
	}

	// Output number to 2 decimal places if it's a float, otherwise output to fixed number.
	const formatNumber = (number) => Number(Number.isInteger(number) ? number.toString() : number.toFixed(2));

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
						value={inputName}
						attribute={getAttrKey('inputName', attributes, manifest)}
						disabledOptions={inputDisabledOptions}
						setAttributes={setAttributes}
						type='input'
						isChanged={isNameChanged}
						setIsChanged={setIsNameChanged}
					/>

					<Container standalone>
						<OptionSelect
							icon={rename}
							label={__('Type', 'eightshift-forms')}
							value={inputType}
							options={getOption('inputType', attributes, manifest)}
							disabled={isOptionDisabled(getAttrKey('inputType', attributes, manifest), inputDisabledOptions)}
							onChange={(value) => {
								setAttributes({ [getAttrKey('inputType', attributes, manifest)]: value });

								setAttributes({ [getAttrKey('inputIsEmail', attributes, manifest)]: false });
								setAttributes({ [getAttrKey('inputIsNumber', attributes, manifest)]: false });
								setAttributes({ [getAttrKey('inputIsUrl', attributes, manifest)]: false });

								if (value === 'email') {
									setAttributes({ [getAttrKey('inputIsEmail', attributes, manifest)]: true });
								}

								if (value === 'number' || value === 'range') {
									setAttributes({ [getAttrKey('inputIsNumber', attributes, manifest)]: true });
								}

								if (value === 'url') {
									setAttributes({ [getAttrKey('inputIsUrl', attributes, manifest)]: true });
								}

								setAttributes({ [getAttrKey('inputRangeUseCustomField', attributes, manifest)]: undefined });
							}}
							type='menu'
							inline
						/>
					</Container>

					<Container standalone>
						<InputField
							icon={titleGeneric}
							label={__('Initial value', 'eightshift-forms')}
							value={inputValue}
							onChange={(value) => setAttributes({ [getAttrKey('inputValue', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('inputValue', attributes, manifest), inputDisabledOptions)}
							inline
						/>
					</Container>

					<ContainerGroup>
						<FieldOptionsVisibility
							{...props('field', attributes, {
								fieldDisabledOptions: inputDisabledOptions,
							})}
						/>

						<Container>
							<Toggle
								icon={none}
								label={__('Disabled', 'eightshift-forms')}
								checked={inputIsDisabled}
								onChange={(value) => setAttributes({ [getAttrKey('inputIsDisabled', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('inputIsDisabled', attributes, manifest), inputDisabledOptions)}
							/>
						</Container>
					</ContainerGroup>

					<Container
						hidden={inputType !== 'range'}
						standalone
					>
						<Toggle
							icon={fieldValue}
							label={__('Value input field', 'eightshift-forms')}
							checked={inputRangeUseCustomField}
							onChange={(value) =>
								setAttributes({ [getAttrKey('inputRangeUseCustomField', attributes, manifest)]: value })
							}
						/>
					</Container>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<FieldOptions
						{...props('field', attributes, {
							fieldDisabledOptions: inputDisabledOptions,
						})}
						additionalControls={(hasLabel) => {
							if (!hasLabel || inputType === 'range' || inputUseLabelAsPlaceholder) {
								return null;
							}

							return (
								<Container>
									<InputField
										actions={<HelpTooltip>{__('Shown when the field is empty', 'eightshift-forms')}</HelpTooltip>}
										icon={fieldPlaceholder}
										label={__('Placeholder', 'eightshift-forms')}
										value={inputPlaceholder}
										onChange={(value) =>
											setAttributes({ [getAttrKey('inputPlaceholder', attributes, manifest)]: value })
										}
										disabled={isOptionDisabled(
											getAttrKey('inputPlaceholder', attributes, manifest),
											inputDisabledOptions,
										)}
									/>
								</Container>
							);
						}}
						additionalControlsInner={(hasLabel) => {
							if (!hasLabel || inputType === 'range') {
								return null;
							}

							return (
								<Container>
									<Toggle
										icon={buttonGhost}
										label={__('Show as placeholder', 'eightshift-forms')}
										checked={inputUseLabelAsPlaceholder}
										onChange={(value) => {
											setAttributes({ [getAttrKey('inputPlaceholder', attributes, manifest)]: undefined });
											setAttributes({ [getAttrKey('inputUseLabelAsPlaceholder', attributes, manifest)]: value });
										}}
									/>
								</Container>
							);
						}}
					/>

					<FieldOptionsMore
						{...props('field', attributes, {
							fieldDisabledOptions: inputDisabledOptions,
						})}
					/>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<FieldOptionsLayout
						{...props('field', attributes, {
							fieldDisabledOptions: inputDisabledOptions,
						})}
					/>

					<ContainerGroup
						hidden={inputType !== 'range'}
						label={__('Show', 'eightshift-forms')}
					>
						<Container>
							<Toggle
								icon={rangeMin}
								label={__('Lower range bound', 'eightshift-forms')}
								checked={inputRangeShowMin}
								onChange={(value) => {
									setAttributes({ [getAttrKey('inputRangeShowMin', attributes, manifest)]: value });

									if (!value) {
										setAttributes({ [getAttrKey('inputRangeShowMinPrefix', attributes, manifest)]: undefined });
										setAttributes({ [getAttrKey('inputRangeShowMinSuffix', attributes, manifest)]: undefined });
									}
								}}
								disabled={isOptionDisabled(getAttrKey('inputRangeShowMin', attributes, manifest), inputDisabledOptions)}
							/>
						</Container>

						<Container hidden={!inputRangeShowMin}>
							<InputField
								icon={positionHStart}
								label={__('Prefix', 'eightshift-forms')}
								value={inputRangeShowMinPrefix}
								onChange={(value) =>
									setAttributes({ [getAttrKey('inputRangeShowMinPrefix', attributes, manifest)]: value })
								}
								disabled={isOptionDisabled(
									getAttrKey('inputRangeShowMinPrefix', attributes, manifest),
									inputDisabledOptions,
								)}
								inline
							/>
						</Container>

						<Container hidden={!inputRangeShowMin}>
							<InputField
								icon={positionHEnd}
								label={__('Suffix', 'eightshift-forms')}
								value={inputRangeShowMinSuffix}
								onChange={(value) =>
									setAttributes({ [getAttrKey('inputRangeShowMinSuffix', attributes, manifest)]: value })
								}
								disabled={isOptionDisabled(
									getAttrKey('inputRangeShowMinSuffix', attributes, manifest),
									inputDisabledOptions,
								)}
								inline
							/>
						</Container>
					</ContainerGroup>

					<ContainerGroup hidden={inputType !== 'range'}>
						<Container>
							<Toggle
								icon={rangeMid}
								label={__('Current value', 'eightshift-forms')}
								checked={inputRangeShowCurrent}
								onChange={(value) => {
									setAttributes({ [getAttrKey('inputRangeShowCurrent', attributes, manifest)]: value });

									if (!value) {
										setAttributes({
											[getAttrKey('inputRangeShowCurrentPrefix', attributes, manifest)]: undefined,
										});
										setAttributes({
											[getAttrKey('inputRangeShowCurrentSuffix', attributes, manifest)]: undefined,
										});
									}
								}}
								disabled={isOptionDisabled(
									getAttrKey('inputRangeShowCurrent', attributes, manifest),
									inputDisabledOptions,
								)}
							/>
						</Container>

						<Container hidden={!inputRangeShowCurrent}>
							<InputField
								icon={positionHStart}
								label={__('Prefix', 'eightshift-forms')}
								value={inputRangeShowCurrentPrefix}
								onChange={(value) =>
									setAttributes({ [getAttrKey('inputRangeShowCurrentPrefix', attributes, manifest)]: value })
								}
								disabled={isOptionDisabled(
									getAttrKey('inputRangeShowCurrentPrefix', attributes, manifest),
									inputDisabledOptions,
								)}
								inline
							/>
						</Container>

						<Container hidden={!inputRangeShowCurrent}>
							<InputField
								icon={positionHEnd}
								label={__('Suffix', 'eightshift-forms')}
								value={inputRangeShowCurrentSuffix}
								onChange={(value) =>
									setAttributes({ [getAttrKey('inputRangeShowCurrentSuffix', attributes, manifest)]: value })
								}
								disabled={isOptionDisabled(
									getAttrKey('inputRangeShowCurrentSuffix', attributes, manifest),
									inputDisabledOptions,
								)}
								inline
							/>
						</Container>
					</ContainerGroup>

					<ContainerGroup hidden={inputType !== 'range'}>
						<Container>
							<Toggle
								icon={rangeMax}
								label={__('Upper range bound', 'eightshift-forms')}
								checked={inputRangeShowMax}
								onChange={(value) => {
									setAttributes({ [getAttrKey('inputRangeShowMax', attributes, manifest)]: value });

									if (!value) {
										setAttributes({ [getAttrKey('inputRangeShowMaxPrefix', attributes, manifest)]: undefined });
										setAttributes({ [getAttrKey('inputRangeShowMaxSuffix', attributes, manifest)]: undefined });
									}
								}}
								disabled={isOptionDisabled(getAttrKey('inputRangeShowMax', attributes, manifest), inputDisabledOptions)}
							/>
						</Container>

						<Container hidden={!inputRangeShowMax}>
							<InputField
								icon={positionHStart}
								label={__('Prefix', 'eightshift-forms')}
								value={inputRangeShowMaxPrefix}
								onChange={(value) =>
									setAttributes({ [getAttrKey('inputRangeShowMaxPrefix', attributes, manifest)]: value })
								}
								disabled={isOptionDisabled(
									getAttrKey('inputRangeShowMaxPrefix', attributes, manifest),
									inputDisabledOptions,
								)}
								inline
							/>
						</Container>

						<Container hidden={!inputRangeShowMax}>
							<InputField
								icon={positionHEnd}
								label={__('Suffix', 'eightshift-forms')}
								value={inputRangeShowMaxSuffix}
								onChange={(value) =>
									setAttributes({ [getAttrKey('inputRangeShowMaxSuffix', attributes, manifest)]: value })
								}
								disabled={isOptionDisabled(
									getAttrKey('inputRangeShowMaxSuffix', attributes, manifest),
									inputDisabledOptions,
								)}
								inline
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<Container standalone>
						<Toggle
							icon={requiredAlt}
							label={__('Required', 'eightshift-forms')}
							checked={inputIsRequired}
							onChange={(value) => setAttributes({ [getAttrKey('inputIsRequired', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('inputIsRequired', attributes, manifest), inputDisabledOptions)}
						/>
					</Container>

					<Container
						hidden={!(inputIsUrl || inputIsEmail)}
						standalone
					>
						<OptionSelect
							icon={regex}
							label={__('Match pattern', 'eightshift-forms')}
							options={inputValidationPatternOptions}
							value={inputValidationPattern}
							onChange={(value) =>
								setAttributes({ [getAttrKey('inputValidationPattern', attributes, manifest)]: value })
							}
							disabled={isOptionDisabled(
								getAttrKey('inputValidationPattern', attributes, manifest),
								inputDisabledOptions,
							)}
							type='menu'
							inline
						/>
					</Container>

					<ContainerGroup hidden={['number', 'range'].includes(inputType)}>
						<Container>
							<NumberPicker
								icon={chevronRight}
								label={__('Min. length', 'eightshift-forms')}
								value={inputMinLength}
								onChange={(value) => setAttributes({ [getAttrKey('inputMinLength', attributes, manifest)]: value })}
								min={options.inputMinLength.min}
								max={options.inputMinLength.max}
								step={options.inputMinLength.step}
								disabled={isOptionDisabled(getAttrKey('inputMinLength', attributes, manifest), inputDisabledOptions)}
								placeholder='–'
								inline
							/>
						</Container>

						<Container>
							<NumberPicker
								icon={chevronLeft}
								label={__('Max. length', 'eightshift-forms')}
								value={inputMaxLength}
								onChange={(value) => setAttributes({ [getAttrKey('inputMaxLength', attributes, manifest)]: value })}
								min={options.inputMaxLength.min}
								max={options.inputMaxLength.max}
								step={options.inputMaxLength.step}
								disabled={isOptionDisabled(getAttrKey('inputMaxLength', attributes, manifest), inputDisabledOptions)}
								placeholder='–'
								inline
							/>
						</Container>
					</ContainerGroup>

					<ContainerGroup hidden={inputType !== 'number' && inputType !== 'range'}>
						<Container>
							<NumberPicker
								icon={chevronRight}
								label={__('Min. value', 'eightshift-forms')}
								value={inputMin}
								onChange={(value) =>
									setAttributes({ [getAttrKey('inputMin', attributes, manifest)]: formatNumber(value) })
								}
								min={options.inputMin.min}
								max={options.inputMin.max}
								step={options.inputMin.step}
								disabled={isOptionDisabled(getAttrKey('inputMin', attributes, manifest), inputDisabledOptions)}
								inline
							/>
						</Container>

						<Container>
							<NumberPicker
								icon={chevronLeft}
								label={__('Max. value', 'eightshift-forms')}
								value={inputMax}
								onChange={(value) =>
									setAttributes({ [getAttrKey('inputMax', attributes, manifest)]: formatNumber(value) })
								}
								min={options.inputMax.min}
								max={options.inputMax.max}
								step={options.inputMax.step}
								disabled={isOptionDisabled(getAttrKey('inputMax', attributes, manifest), inputDisabledOptions)}
								inline
							/>
						</Container>

						<Container>
							<NumberPicker
								icon={step}
								label={__('Step', 'eightshift-forms')}
								value={inputStep}
								onChange={(value) => setAttributes({ [getAttrKey('inputStep', attributes, manifest)]: value })}
								min={options.inputStep.min}
								max={options.inputStep.max}
								step={options.inputStep.step}
								disabled={isOptionDisabled(getAttrKey('inputStep', attributes, manifest), inputDisabledOptions)}
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
							conditionalTagsBlockName: inputName,
							conditionalTagsIsHidden: checkAttr('inputFieldHidden', attributes, manifest),
						})}
					/>

					<ContainerGroup label={__('Tracking', 'eightshift-forms')}>
						<Container>
							<InputField
								icon={googleTagManager}
								label={__('GTM tracking code', 'eightshift-forms')}
								value={inputTracking}
								onChange={(value) => setAttributes({ [getAttrKey('inputTracking', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('inputTracking', attributes, manifest), inputDisabledOptions)}
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>
		</Tabs>
	);
};
