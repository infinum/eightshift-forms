/* global esFormsLocalization */

import { __ } from '@wordpress/i18n';
import { select, dispatch } from "@wordpress/data";
import apiFetch from '@wordpress/api-fetch';
import { createBlock, createBlocksFromInnerBlocksTemplate } from '@wordpress/blocks';
import { camelize, STORE_NAME, InlineNotification, InlineNotificationType } from '@eightshift/frontend-libs/scripts';

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
};

/**
 * Update/create new integration blocks by getting the update from rest api.
 *
 * @param {int} clientId Client ID from block editor.
 * @param {string} postId Post ID to get data from.
 * @param {string} type Integration type.
 * @param {string} itemId Integration internal ID.
 * @param {string} innerId Integration internal alternative ID.
 *
 * @returns {void}
 */
export const updateIntegrationBlocks = (clientId, postId, type, itemId, innerId = '') => {
	apiFetch({ path: `${esFormsLocalization.restPrefixProject}${esFormsLocalization.restRoutes.integrationsEditorCreate}/?id=${postId}&type=${type}&itemId=${itemId}&innerId=${innerId}` }).then((response) => {
		resetInnerBlocks(clientId);

		if (response.code === 200) {
			const builtBlocks = createBlocksFromInnerBlocksTemplate(response?.data?.data?.output);

			updateInnerBlocks(clientId, builtBlocks);

			dispatch('core/editor').savePost();
		}
	});
};

/**
 * Sync integration blocks by getting the update from rest api.
 *
 * @param {int} clientId Client ID from block editor.
 * @param {string} postId Post ID to get data from.
 *
 * @returns {void}
 */
export const syncIntegrationBlocks = (clientId, postId) => {
	return apiFetch({ path: `${esFormsLocalization.restPrefixProject}${esFormsLocalization.restRoutes.integrationsEditorSync}/?id=${postId}` }).then((response) => {
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
			};
		}
	});
};

/**
 * Clear transient cache.
 *
 * @param {string} type Cache integration type name.
 *
 * @returns {string}
 */
export const clearTransientCache = (type) => {
	return apiFetch({
		path: `${esFormsLocalization.restPrefixProject}${esFormsLocalization.restRoutes.cacheClear}/`,
		method: 'POST',
		data: {type},
	}).then((response) => {
		return response.message;
	});
};

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
};

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
};

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
};

/**
 * Reset current inner blocks by id.
 *
 * @param {int} clientId Client ID from block editor
 *
 * @returns {void}
 */
export const resetInnerBlocks = (clientId) => {
	updateInnerBlocks(clientId, []);
};

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
};

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
	const output = {};

	for (const [key, value] of Object.entries(attributes)) {
		if (filterAttributes.includes(key)) {
			output[key] = value;
		}
	}

	return {
		...output,
		...appendItems,
	};
};

/**
 * Get active integration block name by checking the paren item.
 *
 * @param {string} clientId Client Id to check.
 *
 * @returns {string}
 */
export const getActiveIntegrationBlockName = (clientId) => {
	return select('core/block-editor').getBlocksByClientId(clientId)?.[0]?.innerBlocks?.[0]?.attributes?.blockName;
};

/**
 * Get additional block name content from filter.
 *
 * @param {string} blockName Block name.
 *
 * @returns {string}
 */
export const getAdditionalContentFilterContent = (blockName) => {
	if (esFormsLocalization?.additionalContent?.[blockName]) {
		return esFormsLocalization?.additionalContent[blockName];
	}

	return '';
};

/**
 * Output select options from array.
 *
 * @param {object} options 
 * @param {*} useEmpty 
 * @returns 
 */
export const getConstantsOptions = (options, useEmpty = false) => {
	const empty = {
		value: '',
		label: '',
	};

	const items = [];
	if (options) {
		for (const [key, value] of Object.entries(options)) {
			items.push({
				'value': key,
				'label': value
			});
		}
	}

	return useEmpty ? [empty, ...items] : items;
};

/**
 * Output select options from array.
 *
 * @param {object} options 
 * @param {bool} useEmpty 
 * @returns 
 */
export const getSettingsJsonOptions = (options, useEmpty = false) => {
	const empty = {
		value: '',
		label: '',
	};

	const items = [];
	if (options) {
		options.map((item) => {
			items.push({
				'value': item[0],
				'label': item[1],
			});
		});
	}

	return useEmpty ? [empty, ...items] : items;
};

/**
 * Outputs notification if name is missing.
 *
 * @param {string} value Field name value.
 *
 * @returns Component
 */
export const MissingName = ({value, isEditor = false}) => {
	return (
		<>
			{!value &&
				<>
				{isEditor &&
					<><br/><br/></>
				}
				<InlineNotification
					text={
						isEditor ? 
						__('Name field is mandatory and your form will not work correctly it\'s not set. Please fill it in the sidebar.', 'eightshift-forms'):
						__('Name field is mandatory and your form will not work correctly it\'s not set.', 'eightshift-forms')
					}
					type={InlineNotificationType.ERROR}
				/>
				</>
			}
		</>
	);
};
