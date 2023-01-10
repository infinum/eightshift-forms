/* global esFormsLocalization */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, Button, BaseControl } from '@wordpress/components';
import { icons, FancyDivider } from '@eightshift/frontend-libs/scripts';
import { resetInnerBlocks, syncIntegrationBlocks } from '../../../components/utils';
import { SettingsButton } from '../../../components/utils/components/settings-button';

export const FormSelectorOptions = ({
	clientId,
	hasInnerBlocks,
	postId,
 }) => {
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

					<BaseControl
						help={__('If you want to sync external integration form with your own click on this button, but make sure you save your current progress because all unsaved changes will be removed.', 'eightshift-forms')}
					>
						<Button
							variant="secondary"
							icon={icons.lineBreakAlt}
							onClick={() => {
								// Sync integration blocks.
								syncIntegrationBlocks(clientId, postId);
							}}
						>
							{__('Sync integration', 'eightshift-forms')} 
						</Button>
					</BaseControl>
				</>
			}
		</PanelBody>
	);
};
