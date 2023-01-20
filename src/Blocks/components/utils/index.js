/* global esFormsLocalization */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { select, dispatch } from "@wordpress/data";
import apiFetch from '@wordpress/api-fetch';
import { createBlock, createBlocksFromInnerBlocksTemplate } from '@wordpress/blocks';
import { camelize, STORE_NAME } from '@eightshift/frontend-libs/scripts';

/**
 * check if block options is disabled by integration or other component.
 *
 * @param {string} key Key to check.
 * @param {array} options Options array to check.
 *
 * @returns {boolean}
 */
export const isOptionDisabled = (key, options) => {
	return options.includes(key);
}

/**
 * Update/create new integration blocks by getting the update from rest api.
 *
 * @param {int} clientId Client ID from block editor.
 * @param {string} postId Post ID to get data from.
 * @param {*} type Integration type.
 * @param {*} itemId Integration internal ID.
 * @param {*} innerId Integration internal alternative ID.
 *
 * @returns {void}
 */
export const updateIntegrationBlocks = (clientId, postId, type, itemId, innerId = '') => {
	apiFetch({ path: `${esFormsLocalization.restPrefix}/integration-editor-create/?id=${postId}&type=${type}&itemId=${itemId}&innerId=${innerId}` }).then((response) => {
		resetInnerBlocks(clientId);

		if (response.code === 200) {
			const builtBlocks = createBlocksFromInnerBlocksTemplate(response?.data?.data?.output);

			updateInnerBlocks(clientId, builtBlocks);
		}
	});
}

/**
 * Sync integration blocks by getting the update from rest api.
 *
 * @param {int} clientId Client ID from block editor.
 * @param {string} postId Post ID to get data from.
 *
 * @returns {void}
 */
export const syncIntegrationBlocks = (clientId, postId) => {
	return apiFetch({ path: `${esFormsLocalization.restPrefix}/integration-editor-sync/?id=${postId}` }).then((response) => {
		resetInnerBlocks(clientId);

		if (response.code === 200) {
			const builtBlocks = createBlocksFromInnerBlocksTemplate(response?.data?.data?.output);

			updateInnerBlocks(clientId, builtBlocks);

			return {
				update: response?.data?.data?.update,
				removed: response?.data?.data?.removed,
				added: response?.data?.data?.added,
				replaced: response?.data?.data?.replaced,
				changed: response?.data?.data?.changed,
			}
		}
	});
}

/**
 * Get settings page url.
 *
 * @param {string} postId Post ID to get data from.
 *
 * @returns {string}
 */
export const getSettingsPageUrl = (postId) => {
	const wpAdminUrl = esFormsLocalization.wpAdminUrl;

	const {
		settingsPageUrl,
	} = select(STORE_NAME).getSettings();

	return `${wpAdminUrl}${settingsPageUrl}&formId=${postId}`;
}

/**
 * Create new inner blocks from provided template.
 *
 * @param {int} clientId Client ID from block editor.
 * @param {string} name Block name tamplete is set to.
 * @param {array} templates Templates of blocks to build.
 *
 * @returns {void}
 */
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

/**
 * Update inner blocks state by id.
 *
 * @param {int} clientId Client ID from block editor.
 * @param {array} blocks Blocks to append.
 *
 * @returns {void}
 */
export const updateInnerBlocks = (clientId, blocks) => {
	dispatch( 'core/block-editor' ).replaceInnerBlocks( clientId, blocks);
}

/**
 * Reset current inner blocks by id.
 *
 * @param {int} clientId Client ID from block editor
 *
 * @returns {void}
 */
export const resetInnerBlocks = (clientId) => {
	updateInnerBlocks(clientId, []);
}

/**
 * Get additional content filte values.
 *
 * @param {string} key Key to find in global constant.
 *
 * @returns {string}
 */
export const getAdditionalContent = (key) => {
	if (typeof esFormsLocalization !== 'undefined' && (esFormsLocalization?.[key]) !== '') {
		return esFormsLocalization[key];
	}

	return '';
}

/**
 * Get forms fields blocks by checking the current block editor state.
 *
 * @returns {object}
 */
export const getFormFields = () => {
	const blocks = select('core/block-editor').getBlocks();

	const fields = blocks?.[0]?.innerBlocks?.[0]?.innerBlocks ?? [];

	if (!fields) {
		return [];
	}

	return [
		{
			value: '',
			label: '',
		},
		...fields.map((item) => {
			const {
				attributes,
				attributes: {
					blockName,
				}
			} = item;

			const value = attributes[camelize(`${blockName}-${blockName}-name`)];
			let label = attributes[camelize(`${blockName}-${blockName}-field-label`)];

			if (value === 'submit') {
				return;
			}

			if (label === 'Label') {
				label = value;
			}

			return {
				'label': label,
				'value': value,
			};
		}).filter((elm) => elm),
	];
}

/**
 * Filter attributes by array of keys. Used to provide alternative attributes to server side render component to prevent unecesery rerender.
 *
 * @param {object} attributes Attributes data source.
 * @param {array} filterAttributes Array of attributes to filter.
 * @param {object} appendItems Append additional attributes.
 *
 * @returns {object}
 */
export const getFilteredAttributes = (attributes, filterAttributes, appendItems = {}) => {
	const output = {}

	for (const [key, value] of Object.entries(attributes)) {
		if (filterAttributes.includes(key)) {
			output[key] = value;
		}
	}

	return {
		...output,
		...appendItems,
	}
}
