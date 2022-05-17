import React from 'react';
import { __ } from '@wordpress/i18n';
import { select } from "@wordpress/data";
import { PanelBody, BaseControl, Button } from '@wordpress/components';
import { IconLabel, icons, STORE_NAME } from '@eightshift/frontend-libs/scripts';

export const MailchimpOptions = ({ postId }) => {
	const {
		settingsPageUrl,
	} = select(STORE_NAME).getSettings();

	const wpAdminUrl = esFormsLocalization.wpAdminUrl;

	return (
		<PanelBody title={__('Mailchimp', 'eightshift-forms')}>
			<BaseControl
				label={<IconLabel icon={icons.options} label={__('Settings', 'eightshift-forms')} />}
				help={__('On Mailchimp settings page you can setup all details regarding you integration.', 'eightshift-forms')}
			>
				<Button
					href={`${wpAdminUrl}${settingsPageUrl}&formId=${postId}&type=mailchimp`}
					isSecondary
				>
					{__('Open Mailchimp Form Settings', 'eightshift-forms')}
				</Button>
			</BaseControl>
		</PanelBody>
	);
};
