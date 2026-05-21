import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey, MediaPicker, props } from '@eightshift/frontend-libs-tailwind/scripts';
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
	RichLabel,
	BaseControl,
	Switch,
} from '@eightshift/ui-components';
import {
	a11yWarning,
	checkSquare,
	googleTagManager,
	hide,
	none,
	sliders,
	tag,
	design,
	moreH,
	iconGeneric,
} from '@eightshift/ui-components/icons';
import { isOptionDisabled, NameField } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import manifest from '../manifest.json';

export const CheckboxOptions = (attributes) => {
	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const checkboxLabel = checkAttr('checkboxLabel', attributes, manifest);
	const checkboxValue = checkAttr('checkboxValue', attributes, manifest);
	const checkboxIsChecked = checkAttr('checkboxIsChecked', attributes, manifest);
	const checkboxIsDisabled = checkAttr('checkboxIsDisabled', attributes, manifest);
	const checkboxTracking = checkAttr('checkboxTracking', attributes, manifest);
	const checkboxDisabledOptions = checkAttr('checkboxDisabledOptions', attributes, manifest);
	const checkboxIcon = checkAttr('checkboxIcon', attributes, manifest);
	const checkboxIconId = checkAttr('checkboxIconId', attributes, manifest);
	const checkboxHideLabelText = checkAttr('checkboxHideLabelText', attributes, manifest);
	const checkboxIsHidden = checkAttr('checkboxIsHidden', attributes, manifest);

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
						value={checkboxValue}
						attribute={getAttrKey('checkboxValue', attributes, manifest)}
						disabledOptions={checkboxDisabledOptions}
						setAttributes={setAttributes}
						label={__('Value', 'eightshift-forms')}
						type='checkbox'
						isChanged={isNameChanged}
						setIsChanged={setIsNameChanged}
					/>

					<Container standalone>
						<Toggle
							icon={checkSquare}
							label={__('Selected', 'eightshift-forms')}
							checked={checkboxIsChecked}
							onChange={(value) => setAttributes({ [getAttrKey('checkboxIsChecked', attributes, manifest)]: value })}
							disabled={isOptionDisabled(
								getAttrKey('checkboxIsChecked', attributes, manifest),
								checkboxDisabledOptions,
							)}
						/>
					</Container>

					<ContainerGroup>
						<Container>
							<Toggle
								icon={hide}
								label={__('Hidden', 'eightshift-forms')}
								checked={checkboxIsHidden}
								onChange={(value) => setAttributes({ [getAttrKey('checkboxIsHidden', attributes, manifest)]: value })}
								disabled={isOptionDisabled(
									getAttrKey('checkboxIsHidden', attributes, manifest),
									checkboxDisabledOptions,
								)}
							/>
						</Container>

						<Container>
							<Toggle
								icon={none}
								label={__('Disabled', 'eightshift-forms')}
								checked={checkboxIsDisabled}
								onChange={(value) => setAttributes({ [getAttrKey('checkboxIsDisabled', attributes, manifest)]: value })}
								disabled={isOptionDisabled(
									getAttrKey('checkboxIsDisabled', attributes, manifest),
									checkboxDisabledOptions,
								)}
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
								value={checkboxHideLabelText ? null : checkboxLabel}
								onChange={(value) => setAttributes({ [getAttrKey('checkboxLabel', attributes, manifest)]: value })}
								disabled={
									checkboxHideLabelText ||
									isOptionDisabled(getAttrKey('checkboxLabel', attributes, manifest), checkboxDisabledOptions)
								}
								rows={1}
								actions={
									<Switch
										arial-label={__('Show option label', 'eightshift-forms')}
										checked={!checkboxHideLabelText}
										onChange={(value) =>
											setAttributes({ [getAttrKey('checkboxHideLabelText', attributes, manifest)]: !value })
										}
										size='medium'
									/>
								}
							/>
						</Container>

						<Container
							hidden={!checkboxHideLabelText && checkboxLabel?.length > 0}
							className='es-uic-theme-orange'
							elevated
							centered
							accent
						>
							<RichLabel
								label={
									checkboxLabel === ''
										? __('Label should not be empty', 'eightshift-forms')
										: __('Options should have labels for accessibility', 'eightshift-forms')
								}
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
									[getAttrKey('checkboxIcon', attributes, manifest)]: url,
									[getAttrKey('checkboxIconId', attributes, manifest)]: id,
								})
							}
							imageId={checkboxIconId}
							imageUrl={checkboxIcon}
						/>
					</BaseControl>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<ConditionalTagsOptions
						{...props('conditionalTags', attributes, {
							conditionalTagsBlockName: checkboxValue,
							conditionalTagsIsHidden: checkboxIsHidden,
						})}
					/>

					<ContainerGroup label={__('Tracking', 'eightshift-forms')}>
						<Container>
							<InputField
								icon={googleTagManager}
								label={__('GTM tracking code', 'eightshift-forms')}
								value={checkboxTracking}
								onChange={(value) => setAttributes({ [getAttrKey('checkboxTracking', attributes, manifest)]: value })}
								disabled={isOptionDisabled(
									getAttrKey('checkboxTracking', attributes, manifest),
									checkboxDisabledOptions,
								)}
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>
		</Tabs>
	);
};
