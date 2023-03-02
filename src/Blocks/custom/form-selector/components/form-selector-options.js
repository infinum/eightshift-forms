/* global esFormsLocalization */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { useSelect, select } from "@wordpress/data";
import { PanelBody, Button } from '@wordpress/components';
import { icons, STORE_NAME } from '@eightshift/frontend-libs/scripts';

export const FormSelectorOptions = () => {
	const {
		settingsPageUrl,
	} = select(STORE_NAME).getSettings();

	const formId = useSelect((select) => select('core/editor').getCurrentPostId());
	const wpAdminUrl = esFormsLocalization.wpAdminUrl;

	return (
		<PanelBody title={__('Eightshift Forms', 'eightshift-forms')}>
			<Button
				variant='primary'isPrimary
				icon={icons.options}
				href={`${wpAdminUrl}${settingsPageUrl}&formId=${formId}&type=general`}
				style={{ height: '3rem', paddingLeft: '0.5rem', paddingRight: '0.5rem', }}
			>
				<span>
					<span>{__('Form settings', 'eightshift-forms')}</span>
					<br />
					<small>{__('Configure the form and integrations', 'eightshift-forms')}</small>
				</span>
			</Button>
		</PanelBody>
	);
};
