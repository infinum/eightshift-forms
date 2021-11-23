import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, BaseControl, Button } from '@wordpress/components';
import { IconLabel, icons } from '@eightshift/frontend-libs/scripts';
import globalManifest from '../../../manifest.json';

export const GreenhouseOptions = ({ postId }) => {
	const {
		settingsPageUrl,
	} = globalManifest;

	return (
		<PanelBody title={__('Greenhouse', 'eightshift-forms')}>
			<BaseControl
				label={<IconLabel icon={icons.options} label={__('Settings', 'eightshift-forms')} />}
				help={__('On Greenhouse settings page you can setup all details regarding you integration.', 'eightshift-forms')}
			>
				<Button
					href={`${settingsPageUrl}&formId=${postId}&type=greenhouse`}
					isSecondary
				>
					{__('Open Greenhouse Form Settings', 'eightshift-forms')}
				</Button>
			</BaseControl>
		</PanelBody>
	);
};
