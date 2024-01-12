import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { select, useDispatch, useSelect, dispatch } from "@wordpress/data";
import { store as noticesStore } from '@wordpress/notices';
import { Button, PanelBody, Modal } from '@wordpress/components';
import { icons, Select, Section, props, Control, IconLabel } from '@eightshift/frontend-libs/scripts';
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
				setFormItems(response.data);
			}
		});

		if (innerIdKey && itemId) {
			apiFetch({
				path: `${getRestUrlByType('prefixIntegrationItemsInner', block, true)}?id=${itemId}`,
			}).then((response) => {
				if (response.code === 200) {
					setFormInnerItems(response.data);
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
				className='es-modal-max-width-xxl es-rounded-3!'
				title={<IconLabel icon={icons.clipboard} label={__('Sync report', 'eightshift-forms')} standalone />}
				onRequestClose={() => {
					setModalOpen(false);
					dispatch(FORMS_STORE_NAME).setIsSyncDialogOpen(false);
				}}
			>
				<Section
					showIf={added.length > 0}
					icon={icons.add}
					label={__('Added fields', 'eightshift-forms')}
					additionalLabelClasses='es-nested-bg-green-500!'
					noBottomSpacing={changed.length < 1 && replaced.length < 1 && removed?.length < 1}
				>
					<div className='es-v-spaced'>
						{added.map((item, i) => <IconLabel icon={icons.dummySpacer} label={item} key={i} standalone />)}
					</div>
				</Section>

				<Section
					showIf={removed.length > 0} icon={icons.trash}
					label={__('Removed fields', 'eightshift-forms')}
					additionalLabelClasses='es-nested-bg-red-500!'
					noBottomSpacing={changed.length < 1 && replaced.length < 1}
				>
					<div className='es-v-spaced'>
						{removed.map((item, i) => <IconLabel icon={icons.dummySpacer} label={item} key={i} standalone />)}
					</div>
				</Section>

				<Section
					showIf={replaced.length > 0}
					icon={icons.swap}
					label={__('Replaced fields', 'eightshift-forms')}
					additionalLabelClasses='es-nested-bg-yellow-500!'
					noBottomSpacing={changed.length < 1}
				>
					<div className='es-v-spaced'>
						{replaced.map((item, i) => <IconLabel icon={icons.dummySpacer} label={item} key={i} standalone />)}
					</div>
				</Section>

				<Section
					showIf={changed.length > 0}
					icon={icons.edit}
					label={__('Updated field attributes', 'eightshift-forms')}
					additionalLabelClasses='es-nested-bg-blue-500!'
					noBottomSpacing
				>
					<div className='es-v-spaced'>
						{changed.map((item, i) =>
							<IconLabel
								icon={icons.dummySpacer}
								label={
									<span key={i}>
										<code>{Object.keys(item)[0]}</code>: {Object.values(item)[0].join(', ')}
									</span>
								}
								key={i}
								standalone
							/>)
						}
					</div>
				</Section>
			</Modal>
		);
	};

	return (
		<>
			<PanelBody title={title}>
				<Control>
					<div className='es-fifty-fifty-h es-gap-2!'>
						<SettingsButton />
						<LocationsButton />
					</div>
				</Control>

				<Section icon={icons.tools} label={__('Integration options', 'eightshift-forms')}>
					<Select
						icon={icons.formAlt}
						label={__('Select a form to display', 'eightshift-forms')}
						help={!(innerIdKey && itemId) && __('If you don\'t see a form in the list, start typing its name while the dropdown is open.', 'eightshift-forms')}
						value={itemId}
						options={formItems}
						onChange={(value) => {
							if (innerIdKey) {
								resetInnerBlocks(clientId);
								setAttributes({ [itemIdKey]: value.toString() });
								setAttributes({ [innerIdKey]: undefined });
							} else {
								updateIntegrationBlocks(clientId, postId, block, value.toString());
								setAttributes({ [itemIdKey]: value.toString() });
							}
						}}
						reducedBottomSpacing={innerIdKey && itemId}
						closeMenuAfterSelect
						simpleValue
					/>

					{(innerIdKey && itemId) &&
						<Select
							help={__('If you don\'t see a form in the list, start typing its name while the dropdown is open.', 'eightshift-forms')}
							value={innerId}
							options={formInnerItems}
							onChange={(value) => {
								updateIntegrationBlocks(clientId, postId, block, itemId, value.toString());
								setAttributes({ [innerIdKey]: value.toString() });
							}}
							closeMenuAfterSelect
							simpleValue
						/>
					}

					{hasInnerBlocks &&
						<div className={'es-border-t-gray-300 es-mt-5 es-pt-5'}>
							<Control
								help={__('Syncs the current form with the integration. Unsaved changes will be lost!', 'eightshift-forms')}
								additionalClasses={'es-border-b-gray-300 es-pb-5'}
							>
								<Button
									icon={icons.loopMode}
									onClick={() => {
										// Sync integration blocks.
										syncIntegrationBlocks(clientId, postId).then((val) => {
											if (val?.status === 'error') {
												createNotice(
													'error',
													val?.message,
													{
														type: 'snackbar',
														icon: '❌',
													}
												);
											} else {
												createNotice(
													val?.update ? 'success' : 'info',
													val?.update ? __('Sync complete!', 'eightshift-forms') : __('Nothing synced, form is up-to-date', 'eightshift-forms'),
													{
														type: 'snackbar',
														icon: '✅',
													}
												);
											}
										});
									}}
									className='es-rounded-1 es-border-cool-gray-300 es-hover-border-cool-gray-400 es-transition'
								>
									{__('Sync integration', 'eightshift-forms')}
								</Button>

								{Object.keys(modalContent).length > 0 &&
									<Button
										onClick={() => {
											setModalOpen(true);
											dispatch(FORMS_STORE_NAME).setIsSyncDialogOpen(true);
										}}
										className='es-rounded-1 es-mt-1 es-font-weight-500'
									>
										{__('View changes', 'eightshift-forms')}
									</Button>
								}
							</Control>

							<Control help={__('Integration data is cached to improve editor performance. If a form has been updated, cache should be cleared, followed by a sync.', 'eightshift-forms')}>
								<Button
									icon={icons.data}
									onClick={() => {
										// Sync integration blocks.
										clearTransientCache(block).then((msg) => createNotice('success', msg, {
											type: 'snackbar',
										}));
									}}
									className='es-rounded-1 es-border-cool-gray-300 es-hover-border-cool-gray-400 es-transition'
								>
									{__('Clear cache', 'eightshift-forms')}
								</Button>
							</Control>
						</div>
					}
				</Section>

				<Section icon={icons.warning} label={__('Danger zone', 'eightshift-forms')} noBottomSpacing>
					<Control help={__('If you want to use a different integration for this form. Current configuration will be deleted.', 'eightshift-forms')} noBottomSpacing>
						<Button
							icon={icons.reset}
							onClick={() => {
								// Reset block to original state.
								resetInnerBlocks(clientId, true);
							}}
							className='es-rounded-1 es-border-cool-gray-300 es-hover-border-cool-gray-400 es-transition'
						>
							{__('Reset form', 'eightshift-forms')}
						</Button>
					</Control>
				</Section>

				{isModalOpen &&
					<SyncModal />
				}

			</PanelBody>

			<StepMultiflowOptions
				{...props('step', attributes, {
					setAttributes,
					stepMultiflowPostId: postId,
				})}
			/>
		</>
	);
};
