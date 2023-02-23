import React from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { PanelBody, Button, BaseControl, Modal } from '@wordpress/components';
import { icons, FancyDivider } from '@eightshift/frontend-libs/scripts';
import { resetInnerBlocks, syncIntegrationBlocks, getActiveIntegrationBlockName, clearTransientCache } from '../../../components/utils';
import { SettingsButton } from '../../../components/utils/components/settings-button';

export const FormSelectorOptions = ({
	clientId,
	hasInnerBlocks,
	postId,
 }) => {
	const [isModalOpen, setIsModalOpen] = useState(false);
	const [modalContent, setModalContent] = useState({});
	const [cacheClear, setCacheClear] = useState('');

	const activeIntegration = getActiveIntegrationBlockName(clientId);

	const SyncModal = () => {
		const updated = modalContent?.update ?? false;
		const added = modalContent?.added ?? [];
		const removed = modalContent?.removed ?? [];
		const replaced = modalContent?.replaced ?? [];
		const changed = modalContent?.changed ?? [];

		return (
			<Modal
				title={__('Sync status.', 'eightshift-forms')}
				onRequestClose={() => {
					setIsModalOpen(false);
					setModalContent({});
				}}
			>
				<h4>{__('Did my form synced with external service?', 'eightshift-forms')}</h4>
				<p>{updated ? __('Yes it did! Please don\'t forget to save your form.', 'eightshift-forms') : __('No, everything is up to date.', 'eightshift-forms')}</p>
				<hr />

				{added.length > 0 &&
					<>
						<h4>{__('Fields added', 'eightshift-forms')}:</h4>
						<ul>
								{added.map((item, index) => <li key={index}>- {item}</li>)}
						</ul>
						<hr />
					</>
				}

				{removed.length > 0 &&
					<>
						<h4>{__('Fields removed', 'eightshift-forms')}:</h4>
						<ul>
								{removed.map((item, index) => <li key={index}>- {item}</li>)}
						</ul>
						<hr />
					</>
				}

				{replaced.length > 0 &&
					<>
						<h4>{__('Fields replaced', 'eightshift-forms')}:</h4>
						<ul>
								{replaced.map((item, index) => <li key={index}>- {item}</li>)}
						</ul>
						<hr />
					</>
				}

				{changed.length > 0 &&
					<>
						<h4>{__('Fields attributes changed', 'eightshift-forms')}:</h4>
						<ul>
								{changed.map((item, index) => {
									return (
										<li key={index}>
											- {Object.keys(item)[0]}:
											<ul>
												{Object.values(item)[0].map((inner, innerIndex) => <li key={innerIndex}>--- {inner}</li>)}
											</ul>
										</li>
									);
								})}
						</ul>
						<hr />
					</>
				}
			</Modal>
		);
	};

	return (
		<PanelBody title={__('Eightshift Forms', 'eightshift-forms')}>
			<SettingsButton />

			{hasInnerBlocks &&
				<>
					<FancyDivider label={__('Advanced', 'eightshift-forms')} />

					<BaseControl
						help={__('If you want to use different integration on your form you can click the form reset button but keep in mind that this action will delete all form configuration for the current integration.', 'eightshift-forms')}
					>
						<Button
							variant="secondary"
							icon={icons.trash}
							onClick={() => {
								// Reset block to original state.
								resetInnerBlocks(clientId);
							}}
						>
							{__('Reset form', 'eightshift-forms')} 
						</Button>
					</BaseControl>

					{activeIntegration !== 'mailer' &&
						<>
							<BaseControl
								help={__('If you want to sync external integration form with your own click on this button, but make sure you save your current progress because all unsaved changes will be removed.', 'eightshift-forms')}
							>
								<Button
									variant="secondary"
									icon={icons.lineBreakAlt}
									onClick={() => {
										// Sync integration blocks.
										syncIntegrationBlocks(clientId, postId).then((val) => {
											setIsModalOpen(true);
											setModalContent(val);
										});
									}}
								>
									{__('Sync integration', 'eightshift-forms')} 
								</Button>
							</BaseControl>

							<BaseControl
								help={__('We cache integration date for faster user experience, if you updated your integrations fields you should first clear cache and then run sync.', 'eightshift-forms')}
								>
								<Button
									variant="secondary"
									icon={icons.lineBreakAlt}
									onClick={() => {
										// Sync integration blocks.
										setCacheClear('');
										clearTransientCache(activeIntegration).then((msg) => setCacheClear(msg));
									}}
								>
									{__('Clear internal cache', 'eightshift-forms')} 
								</Button>
								<br/><br/> {cacheClear}
							</BaseControl>
						</>
					}

					{isModalOpen && <SyncModal />}
				</>
			}
		</PanelBody>
	);
};
