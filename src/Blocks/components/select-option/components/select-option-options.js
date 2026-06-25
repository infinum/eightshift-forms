import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { a11yWarning, checkSquare, hide, none, sliders, tag, moreH } from '@eightshift/ui-components/icons';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { isOptionDisabled, NameField } from './../../utils';
import { ContainerPanel, InputField, Toggle, Tab, TabList, Tabs, TabPanel, Container, ContainerGroup, RichLabel } from '@eightshift/ui-components';
import { ConditionalTagsOptions } from '../../conditional-tags/components/conditional-tags-options';
import manifest from '../manifest.json';

export const SelectOptionOptions = (attributes) => {
	const { setAttributes } = attributes;

	const [isNameChanged, setIsNameChanged] = useState(false);

	const selectOptionLabel = checkAttr('selectOptionLabel', attributes, manifest);
	const selectOptionValue = checkAttr('selectOptionValue', attributes, manifest);
	const selectOptionIsSelected = checkAttr('selectOptionIsSelected', attributes, manifest);
	const selectOptionIsDisabled = checkAttr('selectOptionIsDisabled', attributes, manifest);
	const selectOptionIsHidden = checkAttr('selectOptionIsHidden', attributes, manifest);
	const selectOptionDisabledOptions = checkAttr('selectOptionDisabledOptions', attributes, manifest);

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
					icon={moreH}
					label={__('Advanced', 'eightshift-forms')}
				/>
			</TabList>

			<TabPanel>
				<ContainerPanel>
					<NameField
						value={selectOptionValue}
						attribute={getAttrKey('selectOptionValue', attributes, manifest)}
						disabledOptions={selectOptionDisabledOptions}
						setAttributes={setAttributes}
						type='select-option'
						label={__('Value', 'eightshift-forms')}
						isChanged={isNameChanged}
						setIsChanged={setIsNameChanged}
					/>

					<Container standalone>
						<Toggle
							icon={checkSquare}
							label={__('Selected', 'eightshift-forms')}
							checked={selectOptionIsSelected}
							onChange={(value) => setAttributes({ [getAttrKey('selectOptionIsSelected', attributes, manifest)]: value })}
							disabled={isOptionDisabled(getAttrKey('selectOptionIsSelected', attributes, manifest), selectOptionDisabledOptions)}
						/>
					</Container>

					<ContainerGroup>
						<Container>
							<Toggle
								icon={hide}
								label={__('Hidden', 'eightshift-forms')}
								checked={selectOptionIsHidden}
								onChange={(value) => setAttributes({ [getAttrKey('selectOptionIsHidden', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('selectOptionIsHidden', attributes, manifest), selectOptionDisabledOptions)}
							/>
						</Container>

						<Container>
							<Toggle
								icon={none}
								label={__('Disabled', 'eightshift-forms')}
								checked={selectOptionIsDisabled}
								onChange={(value) => setAttributes({ [getAttrKey('selectOptionIsDisabled', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('selectOptionIsDisabled', attributes, manifest), selectOptionDisabledOptions)}
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
								label={__('Option label', 'eightshift-forms')}
								type='multiline'
								value={selectOptionLabel}
								onChange={(value) => setAttributes({ [getAttrKey('selectOptionLabel', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('selectOptionLabel', attributes, manifest), selectOptionDisabledOptions)}
								rows={1}
							/>
						</Container>

						<Container
							hidden={selectOptionLabel?.length > 0}
							className='es-uic-theme-orange'
							elevated
							centered
							accent
						>
							<RichLabel
								label={__('Label should not be empty', 'eightshift-forms')}
								icon={a11yWarning}
							/>
						</Container>
					</ContainerGroup>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					{' '}
					<ConditionalTagsOptions
						{...props('conditionalTags', attributes, {
							conditionalTagsBlockName: selectOptionValue,
							conditionalTagsIsHidden: selectOptionIsHidden,
						})}
					/>
				</ContainerPanel>
			</TabPanel>
		</Tabs>
	);
};
