/* global esFormsLocalization */

import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { select } from "@wordpress/data";
import { PanelBody, BaseControl, Button } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import {
	icons,
	STORE_NAME,
	CustomSelect,
	checkAttr,
	BlockIcon,
	IconLabel,
	getAttrKey,
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const MailchimpOptions = ({ attributes, setAttributes, postId }) => {
	const {
		settingsPageUrl,
	} = select(STORE_NAME).getSettings();

	const mailchimpIntegrationId = checkAttr('mailchimpIntegrationId', attributes, manifest);

	const wpAdminUrl = esFormsLocalization.wpAdminUrl;

	const [formData, setFormData] = useState([]);

	useEffect( () => {
		apiFetch({ path: 'eightshift-forms/v1/integration-items-mailchimp' }).then((response) => {
			if (response.code === 200) {
				setFormData(response.data);
			}
		});
	}, []);

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

			<CustomSelect
				label={<IconLabel icon={<BlockIcon iconName='esf-form-picker' />} label={__('Form to display', 'eightshift-forms')} />}
				help={__('If you can\'t find a form, start typing its name while the dropdown is open.', 'eightshift-forms')}
				value={mailchimpIntegrationId}
				options={formData}
				onChange={(value) => setAttributes({ [getAttrKey('mailchimpIntegrationId', attributes, manifest)]: value.toString() })}
				isClearable={false}
				cacheOptions={false}
				reFetchOnSearch={true}
				multiple={false}
				simpleValue
			/>
		</PanelBody>
	);
};
