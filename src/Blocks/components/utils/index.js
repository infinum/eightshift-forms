/* global esFormsLocalization */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { select, dispatch } from "@wordpress/data";
import apiFetch from '@wordpress/api-fetch';
import { createBlock, createBlocksFromInnerBlocksTemplate, replaceBlock } from '@wordpress/blocks';
import { STORE_NAME } from '@eightshift/frontend-libs/scripts';


export const isOptionDisabled = (key, options) => {
	return options.includes(key);
}

export const updateIntegrationBlocks = (clientId, postId, type, itemId, innerId = '') => {
	apiFetch({ path: `${esFormsLocalization.restPrefix}/integration-editor-create/?id=${postId}&type=${type}&itemId=${itemId}&innerId=${innerId}` }).then((response) => {
		if (response.code === 200) {
			const builtBlocks = createBlocksFromInnerBlocksTemplate(response.data.output);

			resetInnerBlocks(clientId);

			updateInnerBlocks(clientId, builtBlocks);
		}
	});
}

export const syncIntegrationBlocks = (clientId, postId) => {
	apiFetch({ path: `${esFormsLocalization.restPrefix}/integration-editor-sync/?id=${postId}` }).then((response) => {
		if (response.code === 200) {
			const builtBlocks = createBlocksFromInnerBlocksTemplate(response.data.output);

			resetInnerBlocks(clientId);

			updateInnerBlocks(clientId, builtBlocks);
		}
	});
}

export const getSettingsPageUrl = (postId) => {
	const wpAdminUrl = esFormsLocalization.wpAdminUrl;

	const {
		settingsPageUrl,
	} = select(STORE_NAME).getSettings();

	return `${wpAdminUrl}${settingsPageUrl}&formId=${postId}`;
}

export const createBlockFromTemplate = (clientId, name, templates) => {
	const {
		blockName,
		attributes = {},
		innerBlocks = [],
	} = templates.filter((form) => form.slug === name)[0];

	// Build all inner blocks.
	const inner = innerBlocks.map((item) => createBlock(item[0], item[1] ?? {}, item[2] ?? []));

	// Build top level block.
	const block = createBlock(blockName, attributes, inner);

	// Insert built block in DOM.
	dispatch('core/block-editor').insertBlock(block, 0, clientId);
}

export const updateInnerBlocks = (clientId, blocks) => {
	dispatch( 'core/block-editor' ).replaceInnerBlocks( clientId, blocks);
}

export const resetInnerBlocks = (clientId) => {
	updateInnerBlocks(clientId, []);
}

export const getAdditionalContent = (key) => {
	if (typeof esFormsLocalization !== 'undefined' && (esFormsLocalization?.[key]) !== '') {
		return esFormsLocalization[key];
	}

	return '';
}
