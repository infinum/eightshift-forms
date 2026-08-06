import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { ContainerPanel, InputField, Toggle, NumberPicker, ContainerGroup, Container, RichLabel, Tab, TabList, Tabs, TabPanel } from '@eightshift/ui-components';
import { FieldOptions, FieldOptionsMore, FieldOptionsLayout, FieldOptionsVisibility } from '../../field/components/field-options';
import { isOptionDisabled, NameField } from './../../utils';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import { buttonOutline, checks, fileType, files, googleTagManager, infoCircle, lightBulbAlt, none, requiredAlt, design, moreH, sliders, tag, chevronRight, chevronLeft } from '@eightshift/ui-components/icons';
import manifest from '../manifest.json';
import { HelpTooltip } from '../../../assets/scripts/help-tooltip';

export const FileOptions = (attributes) => {
	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const fileName = checkAttr('fileName', attributes, manifest);
	const fileAccept = checkAttr('fileAccept', attributes, manifest);
	const fileIsMultiple = checkAttr('fileIsMultiple', attributes, manifest);
	const fileIsRequired = checkAttr('fileIsRequired', attributes, manifest);
	const fileTracking = checkAttr('fileTracking', attributes, manifest);
	const fileMinSize = checkAttr('fileMinSize', attributes, manifest);
	const fileMaxSize = checkAttr('fileMaxSize', attributes, manifest);
	const fileCustomInfoText = checkAttr('fileCustomInfoText', attributes, manifest);
	const fileCustomInfoButtonText = checkAttr('fileCustomInfoButtonText', attributes, manifest);
	const fileDisabledOptions = checkAttr('fileDisabledOptions', attributes, manifest);
	const fileIsDisabled = checkAttr('fileIsDisabled', attributes, manifest);

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
						value={fileName}
						attribute={getAttrKey('fileName', attributes, manifest)}
						disabledOptions={fileDisabledOptions}
						setAttributes={setAttributes}
						type='file'
						isChanged={isNameChanged}
						setIsChanged={setIsNameChanged}
					/>

					<ContainerGroup>
						<Container>
							<InputField
								icon={fileType}
								label={__('Accepted file types', 'eightshift-forms')}
								value={fileAccept}
								actions={<HelpTooltip>{__('Separate items with a comma', 'eightshift-forms')}</HelpTooltip>}
								placeholder={__('e.g. .jpg,.png,.pdf', 'eightshift-forms')}
								onChange={(value) => setAttributes({ [getAttrKey('fileAccept', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('fileAccept', attributes, manifest), fileDisabledOptions)}
								monospaceFont
							/>
						</Container>

						<Container
							hidden={!fileAccept}
							className='es-uic-theme-blue'
							centered
							elevated
							accent
						>
							<RichLabel
								icon={lightBulbAlt}
								label={__('Specified file types should be uploadable through the WordPress uploader', 'eightshift-forms')}
							/>
						</Container>
					</ContainerGroup>

					<ContainerGroup>
						<Container>
							<Toggle
								icon={files}
								label={__('Allow uploading multiple files', 'eightshift-forms')}
								checked={fileIsMultiple}
								onChange={(value) => setAttributes({ [getAttrKey('fileIsMultiple', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('fileIsMultiple', attributes, manifest), fileDisabledOptions)}
							/>
						</Container>
					</ContainerGroup>

					<ContainerGroup>
						<FieldOptionsVisibility
							{...props('field', attributes, {
								fieldDisabledOptions: fileDisabledOptions,
							})}
						/>

						<Container>
							<Toggle
								icon={none}
								label={__('Disabled', 'eightshift-forms')}
								checked={fileIsDisabled}
								onChange={(value) => setAttributes({ [getAttrKey('fileIsDisabled', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('fileIsDisabled', attributes, manifest), fileDisabledOptions)}
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<FieldOptions
						{...props('field', attributes, {
							fieldDisabledOptions: fileDisabledOptions,
						})}
					/>

					<FieldOptionsMore
						{...props('field', attributes, {
							fieldDisabledOptions: fileDisabledOptions,
						})}
					/>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<FieldOptionsLayout
						{...props('field', attributes, {
							fieldDisabledOptions: fileDisabledOptions,
						})}
					/>

					<ContainerGroup label={__('Labels', 'eightshift-forms')}>
						<Container>
							<InputField
								value={fileCustomInfoText}
								icon={infoCircle}
								label={__('Instructions', 'eightshift-forms')}
								placeholder={__('Drag and drop files here', 'eightshift-forms')}
								onChange={(value) =>
									setAttributes({
										[getAttrKey('fileCustomInfoText', attributes, manifest)]: value,
										[getAttrKey('fileCustomInfoTextUse', attributes, manifest)]: value?.length > 0,
									})
								}
								disabled={isOptionDisabled(getAttrKey('fileCustomInfoText', attributes, manifest), fileDisabledOptions) || isOptionDisabled(getAttrKey('fileCustomInfoTextUse', attributes, manifest), fileDisabledOptions)}
								inline
							/>
						</Container>

						<Container>
							<InputField
								icon={buttonOutline}
								label={__('Upload button', 'eightshift-forms')}
								value={fileCustomInfoButtonText}
								placeholder={__('Add files', 'eightshift-forms')}
								onChange={(value) => setAttributes({ [getAttrKey('fileCustomInfoButtonText', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('fileCustomInfoButtonText', attributes, manifest), fileDisabledOptions)}
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
							checked={fileIsRequired}
							onChange={(value) => setAttributes({ [getAttrKey('fileIsRequired', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('fileIsRequired', attributes, manifest), fileDisabledOptions)}
						/>
					</Container>

					<ContainerGroup>
						<Container>
							<NumberPicker
								icon={chevronRight}
								label={__('Min. file size', 'eightshift-forms')}
								value={fileMinSize}
								type='number'
								onChange={(value) => setAttributes({ [getAttrKey('fileMinSize', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('fileMinSize', attributes, manifest), fileDisabledOptions)}
								suffix='kB'
								inline
							/>
						</Container>

						<Container>
							<NumberPicker
								icon={chevronLeft}
								label={__('Max. file size', 'eightshift-forms')}
								value={fileMaxSize}
								type='number'
								onChange={(value) => setAttributes({ [getAttrKey('fileMaxSize', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('fileMaxSize', attributes, manifest), fileDisabledOptions)}
								suffix='kB'
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
							conditionalTagsBlockName: fileName,
							conditionalTagsIsHidden: checkAttr('fileFieldHidden', attributes, manifest),
						})}
					/>

					<ContainerGroup label={__('Tracking', 'eightshift-forms')}>
						<Container>
							<InputField
								icon={googleTagManager}
								label={__('GTM tracking code', 'eightshift-forms')}
								value={fileTracking}
								onChange={(value) => setAttributes({ [getAttrKey('fileTracking', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('fileTracking', attributes, manifest), fileDisabledOptions)}
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>
		</Tabs>
	);
};
