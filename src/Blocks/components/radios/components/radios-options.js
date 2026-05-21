import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import {
	ContainerPanel,
	InputField,
	Toggle,
	Tab,
	TabList,
	Tabs,
	TabPanel,
	Container,
	ContainerGroup,
	OptionSelect,
} from '@eightshift/ui-components';
import {
	checks,
	fieldPlaceholder,
	googleTagManager,
	optionListAlt,
	requiredAlt,
	buttonGhost,
	design,
	moreH,
	sliders,
	tag,
} from '@eightshift/ui-components/icons';
import {
	FieldOptions,
	FieldOptionsMore,
	FieldOptionsLayout,
	FieldOptionsVisibility,
} from '../../field/components/field-options';
import { isOptionDisabled, NameField } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import { HelpTooltip } from '../../../assets/scripts/help-tooltip';
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
						value={radiosName}
						attribute={getAttrKey('radiosName', attributes, manifest)}
						disabledOptions={radiosDisabledOptions}
						setAttributes={setAttributes}
						type={'radios'}
						isChanged={isNameChanged}
						setIsChanged={setIsNameChanged}
					/>

					<Container standalone>
						<OptionSelect
							icon={optionListAlt}
							label={__('Show as', 'eightshift-forms')}
							value={radiosShowAs}
							options={globalManifest.showAsMap.options.map((item) =>
								item.value === 'radios' ? { ...item, value: '' } : item,
							)}
							disabled={isOptionDisabled(getAttrKey('radiosShowAs', attributes, manifest), radiosDisabledOptions)}
							onChange={(value) => setAttributes({ [getAttrKey('radiosShowAs', attributes, manifest)]: value })}
							type='menu'
							inline
						/>
					</Container>

					<ContainerGroup>
						<FieldOptionsVisibility
							{...props('field', attributes, {
								fieldDisabledOptions: radiosDisabledOptions,
							})}
						/>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<FieldOptions
						{...props('field', attributes, {
							fieldDisabledOptions: radiosDisabledOptions,
						})}
						additionalControls={(hasLabel) => {
							if (!hasLabel || radiosShowAs !== 'select' || radiosUseLabelAsPlaceholder) {
								return null;
							}

							return (
								<Container>
									<InputField
										actions={<HelpTooltip>{__('Shown when the field is empty', 'eightshift-forms')}</HelpTooltip>}
										icon={fieldPlaceholder}
										label={__('Placeholder', 'eightshift-forms')}
										value={radiosPlaceholder}
										onChange={(value) =>
											setAttributes({ [getAttrKey('radiosPlaceholder', attributes, manifest)]: value })
										}
										disabled={isOptionDisabled(
											getAttrKey('radiosPlaceholder', attributes, manifest),
											radiosDisabledOptions,
										)}
									/>
								</Container>
							);
						}}
						additionalControlsInner={(hasLabel) => {
							if (!hasLabel || radiosShowAs !== 'select') {
								return null;
							}

							return (
								<Container>
									<Toggle
										icon={buttonGhost}
										label={__('Show as placeholder', 'eightshift-forms')}
										checked={radiosUseLabelAsPlaceholder}
										onChange={(value) => {
											setAttributes({ [getAttrKey('radiosPlaceholder', attributes, manifest)]: undefined });
											setAttributes({ [getAttrKey('radiosUseLabelAsPlaceholder', attributes, manifest)]: value });
										}}
									/>
								</Container>
							);
						}}
					/>

					<FieldOptionsMore
						{...props('field', attributes, {
							fieldDisabledOptions: radiosDisabledOptions,
						})}
					/>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<FieldOptionsLayout
						{...props('field', attributes, {
							fieldDisabledOptions: radiosDisabledOptions,
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
							checked={radiosIsRequired}
							onChange={(value) => setAttributes({ [getAttrKey('radiosIsRequired', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('radiosIsRequired', attributes, manifest), radiosDisabledOptions)}
						/>
					</Container>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<ConditionalTagsOptions
						{...props('conditionalTags', attributes, {
							conditionalTagsBlockName: radiosName,
							conditionalTagsIsHidden: checkAttr('radiosFieldHidden', attributes, manifest),
						})}
					/>

					<ContainerGroup label={__('Tracking', 'eightshift-forms')}>
						<Container>
							<InputField
								icon={googleTagManager}
								label={__('GTM tracking code', 'eightshift-forms')}
								value={radiosTracking}
								onChange={(value) => setAttributes({ [getAttrKey('radiosTracking', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('radiosTracking', attributes, manifest), radiosDisabledOptions)}
								monospaceFont
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>
		</Tabs>
	);
};
