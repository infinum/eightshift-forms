import React from 'react';
import { __ } from '@wordpress/i18n';
import { useSelect } from "@wordpress/data";
import { PanelBody, BaseControl, Button } from '@wordpress/components';
import { IconLabel, icons } from '@eightshift/frontend-libs/scripts';
import globalManifest from '../../../manifest.json';

export const MailchimpOptions = () => {
	const {
		settingsPageUrl,
	} = globalManifest;

	const formId = useSelect((select) => select('core/editor').getCurrentPostId());

	return (
		<PanelBody title={__('Mailchimp', 'eightshift-forms')}>
			<BaseControl
				label={<IconLabel icon={icons.options} label={__('Settings', 'eightshift-forms')} />}
				help={__('On Mailchimp settings page you can setup all details regarding you integration.', 'eightshift-forms')}
			>
				<Button
					href={`${settingsPageUrl}&formId=${formId}&type=mailchimp`}
					isSecondary
				>
					{__('Open Mailchimp Form Settings', 'eightshift-forms')}
				</Button>
			</BaseControl>
		</PanelBody>
	);
};
