import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { select, useDispatch, useSelect, dispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { Modal } from '@wordpress/components';
import { props } from '@eightshift/frontend-libs-tailwind/scripts';
import { BaseControl, Select, RichLabel, Button, ContainerPanel, ContainerGroup } from '@eightshift/ui-components';
import { icons } from '@eightshift/ui-components/icons';
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

export const IntegrationsOptions = ({
	title,
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

	const { createNotice } = useDispatch(noticesStore);

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
				title={
					<RichLabel
						icon={icons.clipboard}
						label={__('Sync report', 'eightshift-forms')}
					/>
				}
				onRequestClose={() => {
					setModalOpen(false);
					dispatch(FORMS_STORE_NAME).setIsSyncDialogOpen(false);
				}}
			>
				<ContainerGroup
					showIf={added.length > 0}
					icon={icons.add}
					label={__('Added fields', 'eightshift-forms')}
				>
					<div>
						{added.map((item, i) => (
							<RichLabel
								icon={icons.dummySpacer}
								label={item}
								key={i}
							/>
						))}
					</div>
				</ContainerGroup>

				<ContainerGroup
					showIf={removed.length > 0}
					icon={icons.trash}
					label={__('Removed fields', 'eightshift-forms')}
				>
					<div>
						{removed.map((item, i) => (
							<RichLabel
								icon={icons.dummySpacer}
								label={item}
								key={i}
							/>
						))}
					</div>
				</ContainerGroup>

				<ContainerGroup
					showIf={replaced.length > 0}
					icon={icons.swap}
					label={__('Replaced fields', 'eightshift-forms')}
				>
					<div>
						{replaced.map((item, i) => (
							<RichLabel
								icon={icons.dummySpacer}
								label={item}
								key={i}
							/>
						))}
					</div>
				</ContainerGroup>

				<ContainerGroup
					showIf={changed.length > 0}
					icon={icons.edit}
					label={__('Updated field attributes', 'eightshift-forms')}
				>
					<div>
						{changed.map((item, i) => (
							<RichLabel
								icon={icons.dummySpacer}
								label={
									<span key={i}>
										<code>{Object.keys(item)[0]}</code>: {Object.values(item)[0].join(', ')}
									</span>
								}
								key={i}
							/>
						))}
					</div>
				</ContainerGroup>
			</Modal>
		);
	};

	return (
		<>
			<ContainerPanel title={title}>
				<BaseControl>
					<div>
						<SettingsButton />
						<LocationsButton />
					</div>
				</BaseControl>

				<ContainerGroup
					icon={icons.tools}
					label={__('Integration options', 'eightshift-forms')}
				>
					<Select
						icon={icons.formAlt}
						label={__('Select a form to display', 'eightshift-forms')}
						help={
							!(innerIdKey && itemId) &&
							__(
								"If you don't see a form in the list, start typing its name while the dropdown is open.",
								'eightshift-forms',
							)
						}
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
						clearable
					/>

					{innerIdKey && itemId && (
						<Select
							help={__(
								"If you don't see a form in the list, start typing its name while the dropdown is open.",
								'eightshift-forms',
							)}
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
							clearable
						/>
					)}

					{hasInnerBlocks && (
						<div>
							<BaseControl
								help={__(
									'Syncs the current form with the integration. Unsaved changes will be lost!',
									'eightshift-forms',
								)}
							>
								<Button
									icon={icons.loopMode}
									onClick={() => {
										// Sync integration blocks.
										syncIntegrationBlocks(clientId, postId).then((val) => {
											if (val?.status === 'error') {
												createNotice('error', val?.message, {
													type: 'snackbar',
													icon: '❌',
												});
											} else {
												createNotice(
													val?.update ? 'success' : 'info',
													val?.update
														? __('Sync complete!', 'eightshift-forms')
														: __('Nothing synced, form is up-to-date', 'eightshift-forms'),
													{
														type: 'snackbar',
														icon: '✅',
													},
												);
											}
										});
									}}
								>
									{__('Sync integration', 'eightshift-forms')}
								</Button>

								{Object.keys(modalContent).length > 0 && (
									<Button
										onClick={() => {
											setModalOpen(true);
											dispatch(FORMS_STORE_NAME).setIsSyncDialogOpen(true);
										}}
									>
										{__('View changes', 'eightshift-forms')}
									</Button>
								)}
							</BaseControl>

							<BaseControl
								help={__(
									'Integration data is cached to improve editor performance. If a form has been updated, cache should be cleared, followed by a sync.',
									'eightshift-forms',
								)}
							>
								<Button
									icon={icons.data}
									onClick={() => {
										// Sync integration blocks.
										clearTransientCache(block).then((msg) =>
											createNotice('success', msg, {
												type: 'snackbar',
											}),
										);
									}}
								>
									{__('Clear cache', 'eightshift-forms')}
								</Button>
							</BaseControl>
						</div>
					)}
				</ContainerGroup>

				<ContainerGroup
					icon={icons.warning}
					label={__('Danger zone', 'eightshift-forms')}
				>
					<BaseControl
						help={__(
							'If you want to use a different integration for this form. Current configuration will be deleted.',
							'eightshift-forms',
						)}
					>
						<Button
							icon={icons.reset}
							onClick={() => {
								// Reset block to original state.
								resetInnerBlocks(clientId, true);
							}}
						>
							{__('Reset form', 'eightshift-forms')}
						</Button>
					</BaseControl>
				</ContainerGroup>

				{isModalOpen && <SyncModal />}
			</ContainerPanel>

			<StepMultiflowOptions
				{...props('step', attributes, {
					setAttributes,
					stepMultiflowPostId: postId,
				})}
			/>
		</>
	);
};
