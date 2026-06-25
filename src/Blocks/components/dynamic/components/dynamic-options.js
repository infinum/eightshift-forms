import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldOptions, FieldOptionsMore, FieldOptionsLayout, FieldOptionsVisibility } from '../../field/components/field-options';
import { ContainerPanel, InputField, Toggle, ContainerGroup, Tab, TabList, Tabs, TabPanel, Container } from '@eightshift/ui-components';
import { checkCircleFill, checks, design, googleTagManager, moreH, multiple, requiredAlt, sliders, tag } from '@eightshift/ui-components/icons';
import { isOptionDisabled, NameField } from '../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import manifest from '../manifest.json';

export const DynamicOptions = (attributes) => {
	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const dynamicName = checkAttr('dynamicName', attributes, manifest);
	const dynamicType = checkAttr('dynamicType', attributes, manifest);
	const dynamicIsDeactivated = checkAttr('dynamicIsDeactivated', attributes, manifest);
	const dynamicIsRequired = checkAttr('dynamicIsRequired', attributes, manifest);
	const dynamicTracking = checkAttr('dynamicTracking', attributes, manifest);
	const dynamicDisabledOptions = checkAttr('dynamicDisabledOptions', attributes, manifest);
	const dynamicIsMultiple = checkAttr('dynamicIsMultiple', attributes, manifest);

	return (
		<>
			<ContainerPanel>
				<Container
					standalone
					elevated
					accent
				>
					<Toggle
						icon={checkCircleFill}
						label={__('Active', 'eightshift-forms')}
						help={__('All dynamic fields are deactivated by default.', 'eightshift-forms')}
						checked={!dynamicIsDeactivated}
						onChange={(value) => setAttributes({ [getAttrKey('dynamicIsDeactivated', attributes, manifest)]: !value })}
						disabled={isOptionDisabled(getAttrKey('dynamicIsDeactivated', attributes, manifest), dynamicDisabledOptions)}
					/>
				</Container>
			</ContainerPanel>

			<Tabs hidden={dynamicIsDeactivated}>
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
							value={dynamicName}
							attribute={getAttrKey('dynamicName', attributes, manifest)}
							disabledOptions={dynamicDisabledOptions}
							setAttributes={setAttributes}
							type='dynamic'
							isChanged={isNameChanged}
							setIsChanged={setIsNameChanged}
						/>

						<Container
							hidden={dynamicType !== 'select'}
							standalone
						>
							<Toggle
								icon={multiple}
								label={__('Select multiple items', 'eightshift-forms')}
								checked={dynamicIsMultiple}
								onChange={(value) => {
									setAttributes({ [getAttrKey('dynamicIsMultiple', attributes, manifest)]: value });
								}}
								disabled={isOptionDisabled(getAttrKey('dynamicIsMultiple', attributes, manifest), dynamicDisabledOptions)}
							/>
						</Container>

						<ContainerGroup>
							<FieldOptionsVisibility
								{...props('field', attributes, {
									fieldDisabledOptions: dynamicDisabledOptions,
								})}
							/>
						</ContainerGroup>
					</ContainerPanel>
				</TabPanel>

				<TabPanel>
					<ContainerPanel>
						<FieldOptions
							{...props('field', attributes, {
								fieldDisabledOptions: dynamicDisabledOptions,
							})}
						/>

						<FieldOptionsMore
							{...props('field', attributes, {
								fieldDisabledOptions: dynamicDisabledOptions,
							})}
						/>
					</ContainerPanel>
				</TabPanel>

				<TabPanel>
					<ContainerPanel>
						<FieldOptionsLayout
							{...props('field', attributes, {
								fieldDisabledOptions: dynamicDisabledOptions,
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
								checked={dynamicIsRequired}
								onChange={(value) => setAttributes({ [getAttrKey('dynamicIsRequired', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('dynamicIsRequired', attributes, manifest), dynamicDisabledOptions)}
							/>
						</Container>
					</ContainerPanel>
				</TabPanel>

				<TabPanel>
					<ContainerPanel>
						<ConditionalTagsOptions
							{...props('conditionalTags', attributes, {
								conditionalTagsBlockName: dynamicName,
								conditionalTagsIsHidden: checkAttr('dynamicFieldHidden', attributes, manifest),
							})}
						/>

						<ContainerGroup label={__('Tracking', 'eightshift-forms')}>
							<Container>
								<InputField
									icon={googleTagManager}
									label={__('GTM tracking code', 'eightshift-forms')}
									value={dynamicTracking}
									onChange={(value) => setAttributes({ [getAttrKey('dynamicTracking', attributes, manifest)]: value })}
									disabled={isOptionDisabled(getAttrKey('dynamicTracking', attributes, manifest), dynamicDisabledOptions)}
								/>
							</Container>
						</ContainerGroup>
					</ContainerPanel>
				</TabPanel>
			</Tabs>
		</>
	);
};
