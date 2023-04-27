/* global esFormsLocalization */

import React, { useEffect } from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { select } from "@wordpress/data";
import { Button } from '@wordpress/components';
import { icons, Select, Section } from '@eightshift/frontend-libs/scripts';
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

	useEffect(() => {
		apiFetch({
			path:
				`${esFormsLocalization.restPrefixProject}/${esFormsLocalization.restRoutes.integrationsItems}-${block}`
		}).then((response) => {
			if (response.code === 200) {
				setFormItems(response.data);
			}
		});

		if (innerIdKey && itemId) {
			apiFetch({
				path:
					`${esFormsLocalization.restPrefixProject}/${esFormsLocalization.restRoutes.integrationsItemsInner}-${block}/?id=${itemId}`
			}).then((response) => {
				if (response.code === 200) {
					setFormInnerItems(response.data);
				}
			});
		}
	}, [itemId, block, innerIdKey]);

	return (
		<>
			<Select
				icon={icons.formAlt}
				label={__('Form to display', 'eightshift-forms')}
				help={!(innerIdKey && itemId) && __('If you don\'t see a form in the list, start typing its name while the dropdown is open.', 'eightshift-forms')}
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
				}}
				reducedBottomSpacing={innerIdKey && itemId}
				closeMenuAfterSelect
				simpleValue
			/>

			{(innerIdKey && itemId) &&
				<Select
					help={__('If you don\'t see a form in the list, start typing its name while the dropdown is open.', 'eightshift-forms')}
					value={innerId}
					options={formInnerItems}
					onChange={(value) => {
						updateIntegrationBlocks(clientId, postId, block, itemId, value.toString());
						setAttributes({ [innerIdKey]: value.toString() });
					}}
					closeMenuAfterSelect
					simpleValue
				/>
			}

			<Section icon={icons.tools} label={__('Advanced', 'eightshift-forms')} noBottomSpacing>
				<Button
					href={getSettingsPageUrl(postId)}
					icon={icons.options}
					className='es-rounded-1 es-border-cool-gray-300 es-hover-border-cool-gray-400 es-transition'
				>
					{__('Form settings', 'eightshift-forms')}
				</Button>
			</Section>
		</>
	);
};
