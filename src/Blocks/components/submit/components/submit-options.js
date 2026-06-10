import { __ } from '@wordpress/i18n';
import { googleTagManager, none, sliders, tag, design, moreH } from '@eightshift/ui-components/icons';
import { checkAttr, getAttrKey, props } from '@eightshift/frontend-libs-tailwind/scripts';
import { FieldOptionsMore, FieldOptionsLayout, FieldOptionsVisibility } from '../../field/components/field-options';
import { isOptionDisabled } from './../../utils';
import { ContainerPanel, InputField, Toggle, Tab, TabList, Tabs, TabPanel, Container, ContainerGroup } from '@eightshift/ui-components';
import manifest from '../manifest.json';

export const SubmitOptions = (attributes) => {
	const { setAttributes } = attributes;

	const submitValue = checkAttr('submitValue', attributes, manifest);
	const submitIsDisabled = checkAttr('submitIsDisabled', attributes, manifest);
	const submitTracking = checkAttr('submitTracking', attributes, manifest);
	const submitDisabledOptions = checkAttr('submitDisabledOptions', attributes, manifest);

	return (
		<>
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
						<ContainerGroup>
							<FieldOptionsVisibility
								{...props('field', attributes, {
									fieldDisabledOptions: submitDisabledOptions,
								})}
							/>

							<Container>
								<Toggle
									icon={none}
									label={__('Disabled', 'eightshift-forms')}
									checked={submitIsDisabled}
									onChange={(value) => setAttributes({ [getAttrKey('submitIsDisabled', attributes, manifest)]: value })}
									disabled={isOptionDisabled(getAttrKey('submitIsDisabled', attributes, manifest), submitDisabledOptions)}
								/>
							</Container>
						</ContainerGroup>
					</ContainerPanel>
				</TabPanel>

				<TabPanel>
					<ContainerPanel>
						<Container standalone>
							<InputField
								icon={tag}
								label={__('Label', 'eightshift-forms')}
								value={submitValue}
								onChange={(value) => setAttributes({ [getAttrKey('submitValue', attributes, manifest)]: value })}
								disabled={isOptionDisabled(getAttrKey('submitValue', attributes, manifest), submitDisabledOptions)}
							/>
						</Container>

						<FieldOptionsMore
							{...props('field', attributes, {
								fieldDisabledOptions: submitDisabledOptions,
							})}
						/>
					</ContainerPanel>
				</TabPanel>

				<TabPanel>
					<ContainerPanel>
						<FieldOptionsLayout
							{...props('field', attributes, {
								fieldDisabledOptions: submitDisabledOptions,
							})}
						/>
					</ContainerPanel>
				</TabPanel>

				<TabPanel>
					<ContainerPanel>
						<ContainerGroup label={__('Tracking', 'eightshift-forms')}>
							<Container>
								<InputField
									icon={googleTagManager}
									label={__('GTM tracking code', 'eightshift-forms')}
									value={submitTracking}
									onChange={(value) => setAttributes({ [getAttrKey('submitTracking', attributes, manifest)]: value })}
									disabled={isOptionDisabled(getAttrKey('submitTracking', attributes, manifest), submitDisabledOptions)}
									monospaceFont
								/>
							</Container>
						</ContainerGroup>
					</ContainerPanel>
				</TabPanel>
			</Tabs>
		</>
	);
};
