import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, BaseControl, Button } from '@wordpress/components';
import { IconLabel, icons } from '@eightshift/frontend-libs/scripts';
import globalManifest from '../../../manifest.json';

export const GoodbitsOptions = ({ postId }) => {
	const {
		settingsPageUrl,
	} = globalManifest;

	return (
		<PanelBody title={__('Goodbits', 'eightshift-forms')}>
			<BaseControl
				label={<IconLabel icon={icons.options} label={__('Settings', 'eightshift-forms')} />}
				help={__('On Goodbits settings page you can setup all details regarding you integration.', 'eightshift-forms')}
			>
				<Button
					href={`${settingsPageUrl}&formId=${postId}&type=goodbits`}
					isSecondary
				>
					{__('Open Goodbits Form Settings', 'eightshift-forms')}
				</Button>
			</BaseControl>
		</PanelBody>
	);
};
