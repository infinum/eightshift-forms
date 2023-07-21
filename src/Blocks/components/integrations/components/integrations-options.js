import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { select, useDispatch, useSelect } from "@wordpress/data";
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
import { getRestUrlByType, ROUTES } from '../../form/assets/state';
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
	const [isModalOpen, setIsModalOpen] = useState(false);
	const [modalContent, setModalContent] = useState({});

	const { createNotice } = useDispatch(noticesStore);

	useEffect(() => {
		apiFetch({
			path: getRestUrlByType(ROUTES.PREFIX_INTEGRATIONS_ITEMS, block, true),
		}).then((response) => {
			if (response.code === 200) {
				setFormItems(response.data);
			}
		});

		if (innerIdKey && itemId) {
			apiFetch({
				path: `${getRestUrlByType(ROUTES.PREFIX_INTEGRATIONS_ITEMS_INNER, block, true)}?id=${itemId}`,
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
					setIsModalOpen(false);
					setModalContent({});
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
				<Select
					icon={icons.formAlt}
					label={__('Form to display', 'eightshift-forms')}
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

				<Control>
					<div className='es-fifty-fifty-h es-gap-2!'>
						<SettingsButton />
						<LocationsButton />
					</div>
				</Control>

				<Section showIf={hasInnerBlocks} icon={icons.tools} label={__('Advanced', 'eightshift-forms')}>
					<Control help={__('Syncs the current form with the integration. Unsaved changes will be lost!', 'eightshift-forms')}>
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
										setModalContent(val);

										createNotice(
											val?.update ? 'success' : 'info',
											val?.update ? __('Sync complete!', 'eightshift-forms') : __('Nothing synced, form is up-to-date', 'eightshift-forms'),
											{
												type: 'snackbar',
												icon: '✅',
												explicitDismiss: val?.update,
												actions: val?.update ? [
													{
														label: __('View report', 'eightshift-forms'),
														onClick: () => setIsModalOpen(true),
													}
												] : [],
											}
										);
									}
								});
							}}
							className='es-rounded-1 es-border-cool-gray-300 es-hover-border-cool-gray-400 es-transition'
						>
							{__('Sync integration', 'eightshift-forms')}
						</Button>
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

			</PanelBody>

			<StepMultiflowOptions
				{...props('step', attributes, {
					setAttributes,
					stepMultiflowPostId: postId,
				})}
			/>

			{hasInnerBlocks && isModalOpen && <SyncModal />}
		</>
	);
};
