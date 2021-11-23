import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, BaseControl, Button } from '@wordpress/components';
import { IconLabel, icons } from '@eightshift/frontend-libs/scripts';
import globalManifest from '../../../manifest.json';

export const HubspotOptions = ({ postId }) => {
	const {
		settingsPageUrl,
	} = globalManifest;

	return (
		<PanelBody title={__('HubSpot', 'eightshift-forms')}>
			<BaseControl
				label={<IconLabel icon={icons.options} label={__('Settings', 'eightshift-forms')} />}
				help={__('On HubSpot settings page you can setup all details regarding you integration.', 'eightshift-forms')}
			>
				<Button
					href={`${settingsPageUrl}&formId=${postId}&type=hubspot`}
					isSecondary
				>
					{__('Open HubSpot Form Settings', 'eightshift-forms')}
				</Button>
			</BaseControl>
		</PanelBody>
	);
};
