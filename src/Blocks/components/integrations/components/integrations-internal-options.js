import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { props } from '@eightshift/frontend-libs-tailwind/scripts';
import { BaseControl, Button, ContainerPanel, HStack, ButtonGroup, Container, TriggeredPopover, RichLabel, Tabs, TabList, Tab, TabPanel } from '@eightshift/ui-components';
import { LocationsButton, SettingsButton, resetInnerBlocks } from '../../utils';
import { formAlt, moreH, reset, treeAlt2, warning } from '@eightshift/ui-components/icons';
import { FormOptions, FormOptionsAdvanced } from '../../../components/form/components/form-options';
import { StepMultiflowOptions } from '../../step/components/step-multiflow-options';

export const IntegrationsInternalOptions = ({ attributes, setAttributes, clientId }) => {
	const postId = select('core/editor').getCurrentPostId();

	return (
		<Tabs>
			<TabList>
				<Tab
					icon={formAlt}
					label={__('Form', 'eightshift-forms')}
				/>
				<Tab
					icon={treeAlt2}
					label={__('Multi-step/flow', 'eightshift-forms')}
				/>
				<Tab
					icon={moreH}
					label={__('Advanced', 'eightshift-forms')}
				/>
			</TabList>

			<TabPanel>
				<ContainerPanel>
					<ButtonGroup>
						<SettingsButton />
						<LocationsButton />
					</ButtonGroup>

					<FormOptions
						{...props('form', attributes, {
							setAttributes,
						})}
					/>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<StepMultiflowOptions
						{...props('step', attributes, {
							setAttributes,
							stepMultiflowPostId: postId,
						})}
					/>
				</ContainerPanel>
			</TabPanel>

			<TabPanel>
				<ContainerPanel>
					<FormOptionsAdvanced
						{...props('form', attributes, {
							setAttributes,
						})}
					/>

					<Container
						className='es-uic-theme-orange'
						standalone
						elevated
						accent
					>
						<BaseControl
							icon={warning}
							label={__('Danger zone', 'eightshift-forms')}
							inline
						>
							<TriggeredPopover
								triggerButtonLabel={__('Reset form', 'eightshift-forms')}
								triggerButtonIcon={reset}
								triggerButtonProps={{
									className: 'esf:grow',
								}}
								className='esf:max-w-xs esf:p-16'
								wrapperClassName='es-uic-theme-orange'
							>
								<RichLabel
									icon={reset}
									label={__('Reset form?', 'eightshift-forms')}
									subtitle={__('Current configuration will be deleted.', 'eightshift-forms')}
								/>

								<HStack className='esf:justify-end esf:mt-20'>
									<Button
										slot='close'
										type='ghost'
									>
										{__('Cancel', 'eightshift-forms')}
									</Button>

									<Button
										onClick={() => {
											// Reset block to original state.
											resetInnerBlocks(clientId, true);
										}}
										type='selected'
									>
										{__('Reset', 'eightshift-forms')}
									</Button>
								</HStack>
							</TriggeredPopover>
						</BaseControl>
					</Container>
				</ContainerPanel>
			</TabPanel>
		</Tabs>
	);
};
