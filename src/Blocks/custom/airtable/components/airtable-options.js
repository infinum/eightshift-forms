/* global esFormsLocalization */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from "@wordpress/data";
import { PanelBody, BaseControl, Button } from '@wordpress/components';
import { IconLabel, icons, STORE_NAME } from '@eightshift/frontend-libs/scripts';

export const AirtableOptions = ({ postId }) => {
	const {
		settingsPageUrl,
	} = select(STORE_NAME).getSettings();

	const wpAdminUrl = esFormsLocalization.wpAdminUrl;

	return (
		<PanelBody title={__('Airtable', 'eightshift-forms')}>
			<BaseControl
				label={<IconLabel icon={icons.options} label={__('Settings', 'eightshift-forms')} />}
				help={__('On the Airtable settings page, you can set up all details regarding your integration.', 'eightshift-forms')}
			>
				<Button
					href={`${wpAdminUrl}${settingsPageUrl}&formId=${postId}&type=airtable`}
					isSecondary
				>
					{__('Open Airtable Form Settings', 'eightshift-forms')}
				</Button>
			</BaseControl>
		</PanelBody>
	);
};
