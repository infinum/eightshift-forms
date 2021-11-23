import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, BaseControl, Button } from '@wordpress/components';
import { IconLabel, icons } from '@eightshift/frontend-libs/scripts';
import globalManifest from '../../../manifest.json';

export const MailchimpOptions = ({ postId }) => {
	const {
		settingsPageUrl,
	} = globalManifest;

	return (
		<PanelBody title={__('Mailchimp', 'eightshift-forms')}>
			<BaseControl
				label={<IconLabel icon={icons.options} label={__('Settings', 'eightshift-forms')} />}
				help={__('On Mailchimp settings page you can setup all details regarding you integration.', 'eightshift-forms')}
			>
				<Button
					href={`${settingsPageUrl}&formId=${postId}&type=mailchimp`}
					isSecondary
				>
					{__('Open Mailchimp Form Settings', 'eightshift-forms')}
				</Button>
			</BaseControl>
		</PanelBody>
	);
};
