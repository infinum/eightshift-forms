/* global esFormsLocalization */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from "@wordpress/data";
import { PanelBody, BaseControl, Button } from '@wordpress/components';
import { IconLabel, icons, STORE_NAME } from '@eightshift/frontend-libs/scripts';

export const MomentsOptions = ({ postId }) => {
	const {
		settingsPageUrl,
	} = select(STORE_NAME).getSettings();

	const wpAdminUrl = esFormsLocalization.wpAdminUrl;

	return (
		<PanelBody title={__('Moments', 'eightshift-forms')}>
			<BaseControl
				label={<IconLabel icon={icons.options} label={__('Settings', 'eightshift-forms')} />}
				help={__('On Moments settings page you can setup all details regarding you integration.', 'eightshift-forms')}
			>
				<Button
					href={`${wpAdminUrl}${settingsPageUrl}&formId=${postId}&type=moments`}
					isSecondary
				>
					{__('Open Moments Form Settings', 'eightshift-forms')}
				</Button>
			</BaseControl>
		</PanelBody>
	);
};