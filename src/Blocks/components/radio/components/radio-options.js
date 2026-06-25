import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey, MediaPicker, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { a11yWarning, checkCircle, hide, none, sliders, tag, design, moreH, iconGeneric } from '@eightshift/ui-components/icons';
import { ContainerPanel, InputField, Toggle, Tab, TabList, Tabs, TabPanel, Container, ContainerGroup, RichLabel, BaseControl, Switch } from '@eightshift/ui-components';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import { isOptionDisabled, NameField } from './../../utils';
import manifest from '../manifest.json';

export const RadioOptions = (attributes) => {
	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const radioLabel = checkAttr('radioLabel', attributes, manifest);
	const radioValue = checkAttr('radioValue', attributes, manifest);
	const radioIsChecked = checkAttr('radioIsChecked', attributes, manifest);
	const radioIsDisabled = checkAttr('radioIsDisabled', attributes, manifest);
	const radioDisabledOptions = checkAttr('radioDisabledOptions', attributes, manifest);
	const radioIcon = checkAttr('radioIcon', attributes, manifest);
	const radioIconId = checkAttr('radioIconId', attributes, manifest);
	const radioHideLabelText = checkAttr('radioHideLabelText', attributes, manifest);
	const radioIsHidden = checkAttr('radioIsHidden', attributes, manifest);

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
					icon={moreH}
					label={__('Advanced', 'eightshift-forms')}
				/>
			</TabList>

			<TabPanel>
				<ContainerPanel>
					<NameField
						value={radioValue}
						attribute={getAttrKey('radioValue', attributes, manifest)}
						disabledOptions={radioDisabledOptions}
						setAttributes={setAttributes}
						type='radio'
						label={__('Value', 'eightshift-forms')}
						isChanged={isNameChanged}
						setIsChanged={setIsNameChanged}
					/>

					<Container standalone>
						<Toggle
							icon={checkCircle}
							label={__('Selected', 'eightshift-forms')}
							checked={radioIsChecked}
							onChange={(value) => setAttributes({ [getAttrKey('radioIsChecked', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('radioIsChecked', attributes, manifest), radioDisabledOptions)}
						/>
					</Container>

					<ContainerGroup>
						<Container>
							<Toggle
								icon={hide}
								label={__('Hidden', 'eightshift-forms')}
								checked={radioIsHidden}
								onChange={(value) => setAttributes({ [getAttrKey('radioIsHidden', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('radioIsHidden', attributes, manifest), radioDisabledOptions)}
							/>
						</Container>

						<Container>
							<Toggle
								icon={none}
								label={__('Disabled', 'eightshift-forms')}
								checked={radioIsDisabled}
								onChange={(value) => setAttributes({ [getAttrKey('radioIsDisabled', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('radioIsDisabled', attributes, manifest), radioDisabledOptions)}
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<ContainerGroup>
						<Container>
							<InputField
								icon={tag}
								label={__('Label', 'eightshift-forms')}
								type='multiline'
								value={radioHideLabelText ? null : radioLabel}
								onChange={(value) => setAttributes({ [getAttrKey('radioLabel', attributes, manifest)]: value })}
								disabled={radioHideLabelText || isOptionDisabled(getAttrKey('radioLabel', attributes, manifest), radioDisabledOptions)}
								rows={1}
								actions={
									<Switch
										arial-label={__('Show option label', 'eightshift-forms')}
										checked={!radioHideLabelText}
										onChange={(value) => setAttributes({ [getAttrKey('radioHideLabelText', attributes, manifest)]: !value })}
										size='medium'
									/>
								}
							/>
						</Container>

						<Container
							hidden={!radioHideLabelText && radioLabel?.length > 0}
							className='es-uic-theme-orange'
							elevated
							centered
							accent
						>
							<RichLabel
								label={radioLabel === '' ? __('Label should not be empty', 'eightshift-forms') : __('Options should have labels for accessibility', 'eightshift-forms')}
								icon={a11yWarning}
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<BaseControl
						icon={iconGeneric}
						label={__('Field icon', 'eightshift-forms')}
						help={__('Not applicable when field is rendered as a select menu', 'eightshift-forms')}
					>
						<MediaPicker
							onChange={({ id, url }) =>
								setAttributes({
									[getAttrKey('radioIcon', attributes, manifest)]: url,
									[getAttrKey('radioIconId', attributes, manifest)]: id,
								})
							}
							imageId={radioIconId}
							imageUrl={radioIcon}
						/>
					</BaseControl>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<ConditionalTagsOptions
						{...props('conditionalTags', attributes, {
							conditionalTagsBlockName: radioValue,
							conditionalTagsIsHidden: radioIsHidden,
						})}
					/>
				</ContainerPanel>
			</TabPanel>
		</Tabs>
	);
};
