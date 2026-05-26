import { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { select, useSelect, dispatch } from '@wordpress/data';
import { props } from '@eightshift/frontend-libs-tailwind/scripts';
import {
	BaseControl,
	Select,
	RichLabel,
	Button,
	Container,
	ContainerGroup,
	ContainerPanel,
	Modal,
	ButtonGroup,
	Tabs,
	TabList,
	Tab,
	TabPanel,
	TriggeredPopover,
	HStack,
} from '@eightshift/ui-components';
import {
	data,
	formAlt,
	loopMode,
	reset,
	swap,
	warning,
	moreH,
	treeAlt2,
	help,
	plusCircleFill,
	trashAlt,
} from '@eightshift/ui-components/icons';
import {
	updateIntegrationBlocks,
	resetInnerBlocks,
	syncIntegrationBlocks,
	clearTransientCache,
	SettingsButton,
	LocationsButton,
} from '../../utils';
import { getRestUrlByType } from '../../form/assets/state-init';
import { FORMS_STORE_NAME } from './../../../assets/scripts/store';
import { StepMultiflowOptions } from '../../step/components/step-multiflow-options';
import { toast } from 'sonner';

export const IntegrationsOptions = ({
	block,
	attributes,
	setAttributes,
	clientId,
	itemId,
	itemIdKey,
	innerId,
	innerIdKey,
}) => {
	const postId = select('core/editor').getCurrentPostId();

	// Check if form selector has inner blocks.
	const hasInnerBlocks = useSelect((select) => {
		const blocks = select('core/block-editor').getBlock(clientId);

		return blocks?.innerBlocks.length !== 0;
	});

	const [formItems, setFormItems] = useState([]);
	const [formInnerItems, setFormInnerItems] = useState([]);
	const [isModalOpen, setModalOpen] = useState(select(FORMS_STORE_NAME).getIsSyncDialogOpen());
	const [modalContent] = useState(select(FORMS_STORE_NAME).getSyncDialog());

	useEffect(() => {
		apiFetch({
			path: getRestUrlByType('prefixIntegrationItems', block, true),
		}).then((response) => {
			if (response.code === 200) {
				setFormItems(response.data.integrationItems);
			}
		});

		if (innerIdKey && itemId) {
			apiFetch({
				path: `${getRestUrlByType('prefixIntegrationItemsInner', block, true)}?id=${itemId}`,
			}).then((response) => {
				if (response.code === 200) {
					setFormInnerItems(response.data.integrationItems);
				}
			});
		}
	}, [itemId, block, innerIdKey]);

	const SyncModal = () => {
		const added = modalContent?.added ?? [];
		const removed = modalContent?.removed ?? [];
		const replaced = modalContent?.replaced ?? [];
		const changed = modalContent?.changed ?? [];

		return (
			<Modal
				title={__('Sync results', 'eightshift-forms')}
				onOpenChange={(open) => {
					setModalOpen(open);
					dispatch(FORMS_STORE_NAME).setIsSyncDialogOpen(open);
				}}
				open={isModalOpen}
			>
				<ContainerGroup hidden={added.length < 1}>
					<Container
						className='es-uic-theme-green'
						centered
						elevated
						accent
					>
						<RichLabel
							icon={plusCircleFill}
							label={__('Added fields', 'eightshift-forms')}
						/>
					</Container>

					{added.map((item, i) => (
						<Container
							key={i}
							centered
							compact
						>
							{item}
						</Container>
					))}
				</ContainerGroup>

				<ContainerGroup hidden={removed.length < 1}>
					<Container
						className='es-uic-theme-orange'
						centered
						elevated
						accent
					>
						<RichLabel
							icon={trashAlt}
							label={__('Removed fields', 'eightshift-forms')}
						/>
					</Container>

					{removed.map((item, i) => (
						<Container
							key={i}
							centered
							compact
						>
							{item}
						</Container>
					))}
				</ContainerGroup>

				<ContainerGroup hidden={replaced.length < 1}>
					<Container
						className='es-uic-theme-blue'
						centered
						elevated
						accent
					>
						<RichLabel
							icon={swap}
							label={__('Replaced fields', 'eightshift-forms')}
						/>
					</Container>

					{replaced.map((item, i) => (
						<Container
							key={i}
							centered
							compact
						>
							{item}
						</Container>
					))}
				</ContainerGroup>

				<ContainerGroup hidden={changed.length < 1}>
					<Container
						className='es-uic-theme-yellow'
						centered
						elevated
						accent
					>
						<RichLabel
							icon={swap}
							label={__('Updated fields', 'eightshift-forms')}
						/>
					</Container>

					{changed.map((item, i) => (
						<Container
							key={i}
							centered
							compact
						>
							<code>{Object.keys(item)[0]}</code>: {Object.values(item)[0].join(', ')}
						</Container>
					))}
				</ContainerGroup>
			</Modal>
		);
	};

	const hasSecondLevelSelection = innerIdKey && itemId;

	return (
		<>
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
						<Select
							label={hasSecondLevelSelection ? __('Form group', 'eightshift-forms') : __('Form', 'eightshift-forms')}
							aria-label={!hasSecondLevelSelection && __('Form to display', 'eightshift-forms')}
							value={itemId}
							options={formItems}
							onChange={(value) => {
								// On clear action.
								if (!value) {
									resetInnerBlocks(clientId);
									setAttributes({ [itemIdKey]: undefined });
									setAttributes({ [innerIdKey]: undefined });
								} else {
									if (innerIdKey) {
										resetInnerBlocks(clientId);
										setAttributes({ [itemIdKey]: value.toString() });
										setAttributes({ [innerIdKey]: undefined });
									} else {
										updateIntegrationBlocks(clientId, postId, block, value.toString());
										setAttributes({ [itemIdKey]: value.toString() });
									}
								}
							}}
							simpleValue
							searchable
							clearable
						/>

						<Select
							hidden={!hasSecondLevelSelection}
							label={__('Form', 'eightshift-forms')}
							value={innerId}
							options={formInnerItems}
							onChange={(value) => {
								// On clear action.
								if (!value) {
									setAttributes({ [innerIdKey]: undefined });
								} else {
									updateIntegrationBlocks(clientId, postId, block, itemId, value.toString());
									setAttributes({ [innerIdKey]: value.toString() });
								}
							}}
							simpleValue
							searchable
							clearable
						/>

						<ButtonGroup>
							<SettingsButton />
							<LocationsButton />
						</ButtonGroup>
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
						<ContainerGroup>
							<Container hidden={!hasInnerBlocks}>
								<BaseControl
									icon={data}
									label={__('Integration data', 'eightshift-forms')}
									inline
								>
									<ButtonGroup>
										<TriggeredPopover
											triggerButtonLabel={__('Clear cache', 'eightshift-forms')}
											className='esf:max-w-xs esf:p-16'
										>
											<RichLabel
												icon={help}
												label={__('Clear integration cache?', 'eightshift-forms')}
												subtitle={__(
													'Integration data is cached to improve editor performance. If a form has been updated, cache should be cleared, followed by a sync.',
													'eightshift-forms',
												)}
												iconClassName='esf:self-start!'
											/>

											<HStack className='esf:justify-end esf:mt-20'>
												<Button
													slot='close'
													type='ghost'
												>
													{__('Cancel', 'eightshift-forms')}
												</Button>

												<Button
													type='selected'
													onClick={() => {
														// Sync integration blocks.
														clearTransientCache(block).then((msg) => toast.success(msg));
													}}
													slot='close'
												>
													{__('Clear', 'eightshift-forms')}
												</Button>
											</HStack>
										</TriggeredPopover>

										<TriggeredPopover
											triggerButtonLabel={__('Sync', 'eightshift-forms')}
											className='esf:max-w-xs esf:p-16'
										>
											<RichLabel
												icon={loopMode}
												label={__('Re-sync integration?', 'eightshift-forms')}
												subtitle={__('Unsaved changes will be lost', 'eightshift-forms')}
												iconClassName='esf:self-start!'
											/>

											<HStack className='esf:justify-end esf:mt-20'>
												<Button
													slot='close'
													type='ghost'
												>
													{__('Cancel', 'eightshift-forms')}
												</Button>

												<Button
													type='selected'
													onClick={() => {
														// Sync integration blocks.
														syncIntegrationBlocks(clientId, postId).then((val) => {
															if (val?.status === 'error') {
																toast.error(val?.message);
															} else if (val?.update) {
																toast.success(__('Sync complete!', 'eightshift-forms'), {
																	action: {
																		label: __('View changes', 'eightshift-forms'),
																		onClick: () => {
																			setModalOpen(true);
																			dispatch(FORMS_STORE_NAME).setIsSyncDialogOpen(true);
																		},
																	},
																	actionButtonStyle: {
																		borderRadius: '0.75rem',
																	},
																	duration: 6000,
																});
															} else {
																toast.info(__('Nothing synced, form is up-to-date', 'eightshift-forms'));
															}
														});
													}}
													slot='close'
												>
													{__('Sync', 'eightshift-forms')}
												</Button>
											</HStack>
										</TriggeredPopover>
									</ButtonGroup>
								</BaseControl>
							</Container>

							<Container
								className='es-uic-theme-orange'
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
												slot='close'
											>
												{__('Reset', 'eightshift-forms')}
											</Button>
										</HStack>
									</TriggeredPopover>
								</BaseControl>
							</Container>
						</ContainerGroup>
					</ContainerPanel>
				</TabPanel>
			</Tabs>

			<SyncModal />
		</>
	);
};
