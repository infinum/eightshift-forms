/* global esFormsLocalization */

import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { select } from "@wordpress/data";
import { BaseControl, Button } from '@wordpress/components';
import { icons, CustomSelect, BlockIcon, IconLabel, FancyDivider } from '@eightshift/frontend-libs/scripts';
import { updateIntegrationBlocks, getSettingsPageUrl, resetInnerBlocks } from '../../utils';

export const IntegrationsOptions = ({
	block,
	setAttributes,
	clientId,
	itemId,
	itemIdKey,
	innerId,
	innerIdKey,
}) => {

	const postId = select('core/editor').getCurrentPostId();

	const [formItems, setFormItems] = useState([]);
	const [formInnerItems, setFormInnerItems] = useState([]);

	useEffect( () => {
		apiFetch({ path:
			`${esFormsLocalization.restPrefixProject}/${esFormsLocalization.restRoutes.integrationsItems}-${block}` }).then((response) => {
			if (response.code === 200) {
				setFormItems(response.data);
			}
		});

		if (innerIdKey && itemId) {
			apiFetch({ path: 
				`${esFormsLocalization.restPrefixProject}/${esFormsLocalization.restRoutes.integrationsItemsInner}-${block}/?id=${itemId}` }).then((response) => {
				if (response.code === 200) {
					setFormInnerItems(response.data);
				}
			});
		}
	}, [itemId, block, innerIdKey]);

	return (
		<>
			<CustomSelect
				label={<IconLabel icon={<BlockIcon iconName='esf-form-picker' />} label={__('Select a form to display', 'eightshift-forms')} />}
				help={__('If you can\'t find a form, start typing its name while the dropdown is open.', 'eightshift-forms')}
				value={itemId}
				options={formItems}
				onChange={(value) => {
					if (innerIdKey) {
						resetInnerBlocks(clientId);
						setAttributes({ [itemIdKey]: value.toString() });
						setAttributes({ [innerIdKey]: undefined });
					} else {
						updateIntegrationBlocks(clientId, postId, block, value.toString());
						setAttributes({ [itemIdKey]: value.toString() });
					}
				} }
				isClearable={false}
				cacheOptions={false}
				reFetchOnSearch={true}
				multiple={false}
				closeMenuOnSelect={true}
				simpleValue
			/>

			{(innerIdKey && itemId) &&
				<CustomSelect
					label={<IconLabel icon={<BlockIcon iconName='esf-form-picker' />} label={__('Form to display', 'eightshift-forms')} />}
					help={__('If you can\'t find a form, start typing its name while the dropdown is open.', 'eightshift-forms')}
					value={innerId}
					options={formInnerItems}
					onChange={(value) => {
						updateIntegrationBlocks(clientId, postId, block, itemId, value.toString());
						setAttributes({ [innerIdKey]: value.toString() });
					}}
					isClearable={false}
					cacheOptions={false}
					reFetchOnSearch={true}
					multiple={false}
					closeMenuOnSelect={true}
					simpleValue
				/>
			}

			<FancyDivider label={__('Advanced', 'eightshift-forms')} />

			<BaseControl
				help={__('On form settings page you can setup all additional settings regarding you form.', 'eightshift-forms')}
			>
				<Button
					href={getSettingsPageUrl(postId)}
					variant="secondary"
					icon={icons.options}
				>
					{__('Open form settings', 'eightshift-forms')}
				</Button>
			</BaseControl>
		</>
	);
};
