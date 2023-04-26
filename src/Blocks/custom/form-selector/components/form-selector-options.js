import React from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { PanelBody, Button, Modal } from '@wordpress/components';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { Control, IconLabel, icons, Section } from '@eightshift/frontend-libs/scripts';
import { resetInnerBlocks, syncIntegrationBlocks, getActiveIntegrationBlockName, clearTransientCache } from '../../../components/utils';
import { SettingsButton } from '../../../components/utils/components/settings-button';

export const FormSelectorOptions = ({
	clientId,
	hasInnerBlocks,
	postId,
}) => {
	const [isModalOpen, setIsModalOpen] = useState(false);
	const [modalContent, setModalContent] = useState({});

	const { createSuccessNotice, createNotice } = useDispatch(noticesStore);

	const activeIntegration = getActiveIntegrationBlockName(clientId);

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
		<PanelBody title={__('Eightshift Forms', 'eightshift-forms')}>
			<SettingsButton />

			<Section showIf={hasInnerBlocks && activeIntegration !== 'mailer'} icon={icons.tools} label={__('Advanced', 'eightshift-forms')}>
				<Control help={__('Syncs the current form with the integration. Unsaved changes will be lost!', 'eightshift-forms')}>
					<Button
						icon={icons.loopMode}
						onClick={() => {
							// Sync integration blocks.
							syncIntegrationBlocks(clientId, postId).then((val) => {
								console.log(val);
								setModalContent(val);

								createNotice(val?.updated ? 'success' : 'info', val?.updated ? __('Sync complete!', 'eightshift-forms') : __('Nothing synced, form is up-to-date', 'eightshift-forms'), {
									type: 'snackbar',
									explicitDismiss: val?.updated,
									actions: val?.updated ? [
										{
											label: __('View report', 'eightshift-forms'),
											onClick: () => setIsModalOpen(true),
										}
									] : [],
								});
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
							clearTransientCache(activeIntegration).then((msg) => createSuccessNotice(msg, {
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
							resetInnerBlocks(clientId);
						}}
						className='es-rounded-1 es-border-cool-gray-300 es-hover-border-cool-gray-400 es-transition'
					>
						{__('Reset form', 'eightshift-forms')}
					</Button>
				</Control>
			</Section>

			{hasInnerBlocks && isModalOpen && <SyncModal />}
		</PanelBody>
	);
};
