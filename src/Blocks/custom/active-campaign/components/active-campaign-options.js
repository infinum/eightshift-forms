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

export const ActiveCampaignOptions = ({ attributes, setAttributes, postId }) => {
	const {
		settingsPageUrl,
	} = select(STORE_NAME).getSettings();

	const activeCampaignIntegrationId = checkAttr('activeCampaignIntegrationId', attributes, manifest);

	const wpAdminUrl = esFormsLocalization.wpAdminUrl;

	const [formData, setFormData] = useState([]);

	useEffect( () => {
		apiFetch({ path: 'eightshift-forms/v1/integration-items-active-campaign' }).then((response) => {
			if (response.code === 200) {
				setFormData(response.data);
			}
		});
	}, []);

	return (
		<PanelBody title={__('ActiveCampaign', 'eightshift-forms')}>
			<BaseControl
				label={<IconLabel icon={icons.options} label={__('Settings', 'eightshift-forms')} />}
				help={__('On ActiveCampaign settings page you can setup all details regarding you integration.', 'eightshift-forms')}
			>
				<Button
					href={`${wpAdminUrl}${settingsPageUrl}&formId=${postId}&type=active-campaign`}
					isSecondary
				>
					{__('Open ActiveCampaign Form settings', 'eightshift-forms')}
				</Button>
			</BaseControl>

			<CustomSelect
				label={<IconLabel icon={<BlockIcon iconName='esf-form-picker' />} label={__('Form to display', 'eightshift-forms')} />}
				help={__('If you can\'t find a form, start typing its name while the dropdown is open.', 'eightshift-forms')}
				value={activeCampaignIntegrationId}
				options={formData}
				onChange={(value) => setAttributes({
					[getAttrKey('activeCampaignIntegrationId', attributes, manifest)]: value.toString(),
					[getAttrKey('activeCampaignIntegrationInnerId', attributes, manifest)]: undefined,
				})}
				isClearable={false}
				cacheOptions={false}
				reFetchOnSearch={true}
				multiple={false}
				simpleValue
			/>
		</PanelBody>
	);
};
