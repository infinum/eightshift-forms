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

export const AirtableOptions = ({ attributes, setAttributes, postId }) => {
	const {
		settingsPageUrl,
	} = select(STORE_NAME).getSettings();

	const airtableIntegrationId = checkAttr('airtableIntegrationId', attributes, manifest);
	const airtableIntegrationItemId = checkAttr('airtableIntegrationItemId', attributes, manifest);

	const wpAdminUrl = esFormsLocalization.wpAdminUrl;

	const [formData, setFormData] = useState([]);
	const [formDataItems, setFormDataItems] = useState([]);

	useEffect( () => {
		apiFetch({ path: 'eightshift-forms/v1/integration-items-airtable' }).then((response) => {
			if (response.code === 200) {
				setFormData(response.data);
			}
		});

		if (airtableIntegrationId) {
			apiFetch({ path: `eightshift-forms/v1/integration-item-airtable/?id=${airtableIntegrationId}` }).then((response) => {
				if (response.code === 200) {
					setFormDataItems(response.data);
				}
			});
		}
	}, []);

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

			<CustomSelect
				label={<IconLabel icon={<BlockIcon iconName='esf-form-picker' />} label={__('Form to display', 'eightshift-forms')} />}
				help={__('If you can\'t find a form, start typing its name while the dropdown is open.', 'eightshift-forms')}
				value={airtableIntegrationId}
				options={formData}
				onChange={(value) => setAttributes({
					[getAttrKey('airtableIntegrationId', attributes, manifest)]: value.toString(),
					[getAttrKey('airtableIntegrationItemId', attributes, manifest)]: undefined,
				})}
				isClearable={false}
				cacheOptions={false}
				reFetchOnSearch={true}
				multiple={false}
				simpleValue
			/>

			{airtableIntegrationId &&
				<CustomSelect
					label={<IconLabel icon={<BlockIcon iconName='esf-form-picker' />} label={__('Form to display', 'eightshift-forms')} />}
					help={__('If you can\'t find a form, start typing its name while the dropdown is open.', 'eightshift-forms')}
					value={airtableIntegrationItemId}
					options={formDataItems}
					onChange={(value) => setAttributes({ [getAttrKey('airtableIntegrationItemId', attributes, manifest)]: value.toString() })}
					isClearable={false}
					cacheOptions={false}
					reFetchOnSearch={true}
					multiple={false}
					simpleValue
				/>
			}
		</PanelBody>
	);
};
