/* global esFormsLocalization */

import React, { useEffect } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { select, dispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { Tooltip } from '@wordpress/components';
import { createBlock, createBlocksFromInnerBlocksTemplate } from '@wordpress/blocks';
import { icons } from '@eightshift/ui-components/icons';
import { STORE_NAME, lockPostEditing, unlockPostEditing, getUnique } from '@eightshift/frontend-libs-tailwind/scripts';
import { AnimatedVisibility, RichLabel, Notice, Button, InputField } from '@eightshift/ui-components';
import { unescapeHTML, camelCase } from '@eightshift/ui-components/utilities';
import { FORMS_STORE_NAME } from './../../assets/scripts/store';
import { getRestUrl, getRestUrlByType, getUtilsIcons } from '../form/assets/state-init';
import globalSettings from './../../manifest.json';

/**
 * check if block options is disabled by integration or other component.
 *
 * @param {string} key Key to check.
 * @param {array} options Options array to check.
 *
 * @returns {boolean}
 */
export const isDeveloperMode = () => {
	return Boolean(esFormsLocalization?.isDeveloperMode);
};

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
	apiFetch({
		path: `${getRestUrlByType('prefixIntegrationEditor', 'integrationsEditorCreate', true, true)}?id=${postId}&type=${type}&itemId=${itemId}&innerId=${innerId}`,
	}).then((response) => {
		resetInnerBlocks(clientId);

		if (response.code === 200) {
			const builtBlocks = createBlocksFromInnerBlocksTemplate(response?.data?.syncForm?.data?.output);

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
	return apiFetch({
		path: `${getRestUrlByType('prefixIntegrationEditor', 'integrationsEditorSync', true, true)}?id=${postId}`,
	}).then((response) => {
		if (isDeveloperMode()) {
			console.info(response);
		}

		dispatch(FORMS_STORE_NAME).setSyncDialog({});

		if (response.code === 200) {
			const parentId = select('core/block-editor').getBlockParents(clientId)?.[0];

			if (parentId) {
				resetInnerBlocks(parentId);
				updateInnerBlocks(parentId, createBlocksFromInnerBlocksTemplate(response?.data?.syncForm?.data?.output));

				const blocks = select('core/block-editor').getBlocks(parentId);

				if (blocks) {
					dispatch('core/block-editor').selectBlock(blocks?.[0].clientId);
				}
			}
		}

		if (!response?.data?.syncForm?.data?.update) {
			dispatch(FORMS_STORE_NAME).setSyncDialog({});
		} else {
			dispatch(FORMS_STORE_NAME).setSyncDialog({
				update: response?.data?.syncForm?.data?.update,
				removed: response?.data?.syncForm?.data?.removed,
				added: response?.data?.syncForm?.data?.added,
				replaced: response?.data?.syncForm?.data?.replaced,
				changed: response?.data?.syncForm?.data?.changed,
			});
		}

		return {
			message: response?.message,
			debugType: response?.data?.syncForm?.debugType,
			status: response?.status,
			update: response?.data?.syncForm?.data?.update,
		};
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
		path: getRestUrl('cacheClear', true),
		method: 'POST',
		data: { type },
	}).then((response) => {
		return response.message;
	});
};

/**
 * Create new inner blocks from provided template.
 *
 * @param {int} clientId Client ID from block editor.
 * @param {string} name Block name template is set to.
 * @param {array} templates Templates of blocks to build.
 *
 * @returns {void}
 */
export const createBlockFromTemplate = (clientId, name, templates) => {
	const { blockName, attributes = {}, innerBlocks = [] } = templates.filter((form) => form.slug === name)[0];

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
	dispatch('core/block-editor').replaceInnerBlocks(clientId, blocks);
};

/**
 * Reset current inner blocks by id.
 *
 * @param {int} clientId Client ID from block editor
 *
 * @returns {void}
 */
export const resetInnerBlocks = (clientId, useParent = false) => {
	if (useParent) {
		const parentId = select('core/block-editor').getBlockParents(clientId)?.[0];

		if (parentId) {
			updateInnerBlocks(parentId, []);
		}
	} else {
		updateInnerBlocks(clientId, []);
	}
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
		...fields
			.map((item) => {
				const {
					attributes,
					attributes: { blockName },
				} = item;

				const value = attributes[camelCase(`${blockName}-${blockName}-name`)];
				let label = attributes[camelCase(`${blockName}-${blockName}-field-label`)];

				if (value === 'submit') {
					return null;
				}

				if (label === 'Label') {
					label = value;
				}

				return {
					label: label,
					value: value,
				};
			})
			.filter((elm) => elm),
	];
};

/**
 * Filter attributes by array of keys. Used to provide alternative attributes to server side render component to prevent unnecessary rerender.
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
				value: key,
				label: value,
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
				value: item[0],
				label: item[1],
			});
		});
	}

	return useEmpty ? [empty, ...items] : items;
};

/**
 * Outputs notification if name is missing.
 *
 * @param {string} value Field name value.
 * @param {bool} asPlaceholder If this is a placeholder.
 * @param {bool} isOptional If this is an optional field.
 *
 * @returns Component
 */
export const MissingName = ({ value, asPlaceholder, isOptional = false }) => {
	if (value || asPlaceholder) {
		return null;
	}

	return (
		<div>
			<Tooltip
				text={
					!isOptional
						? __('Name not set!', 'eightshift-forms')
						: __('If you are using conditional tags you must set name on this field.', 'eightshift-forms')
				}
			>
				{React.cloneElement(icons.warning)}
			</Tooltip>
		</div>
	);
};

export const StatusFieldOutput = ({ components }) => {
	if (!components) {
		return null;
	}

	return (
		<div className='esf:absolute! esf:-bottom-10! esf:-right-10! esf:flex! esf:gap-4!'>
			{components.map((component) => {
				if (!component) {
					return null;
				}

				return (
					<div
						className='esf:bg-accent-600! esf:rounded-full! esf:p-5! esf:text-white!'
						key={component.key}
					>
						{component}
					</div>
				);
			})}
		</div>
	);
};

/**
 * Outputs notification if status is conditionals.
 *
 * @param {bool} value Field value.
 *
 * @returns Component
 */
export const StatusIconConditionals = () => {
	return icons.conditionalVisibility;
};

export const StatusIconHidden = () => {
	return icons.hide;
};

export const StatusIconMissingName = () => {
	return icons.warning;
};

/**
 * "Name" option with optional "Required" notification.
 *
 * @param {string} value Field name value.
 * @param {string} attribute Field name attribute.
 * @param {string} help Field help text.
 * @param {array} disabledOptions Array of disabled options.
 * @param {string} label Field label.
 * @param {function} setAttributes Set attributes function.
 * @param {bool} show Show this field.
 * @param {string} type Type of this field.
 *
 * @returns Component
 */
export const NameField = ({
	value,
	attribute,
	help = '',
	disabledOptions = [],
	label,
	setAttributes,
	show = true,
	type,
	isChanged = false,
	isOptional = false,
	setIsChanged,
}) => {
	const isDisabled = isOptionDisabled(attribute, disabledOptions);

	const NameFieldLabel = () => {
		let labelTipText = !isOptional
			? __('The form may not work correctly.', 'eightshift-forms')
			: __('Name field is required only if you are using conditional tags on this field.', 'eightshift-forms');

		if (type === 'resultOutputItem') {
			labelTipText = __(
				`Variable name you can use is "${globalSettings.enums.successRedirectUrlKeys.variation}" or any other provided by the plugins' add-on.`,
				'eightshift-forms',
			);
		}

		return (
			<div>
				<RichLabel
					icon={icons.idCard}
					label={label ? label : __('Name', 'eightshift-forms')}
				/>

				<AnimatedVisibility visible={!value}>
					<Tooltip text={labelTipText}>
						{!isOptional ? (
							<span>{__('Required', 'eightshift-forms')}</span>
						) : (
							<span>{__('Optional', 'eightshift-forms')}</span>
						)}
					</Tooltip>
				</AnimatedVisibility>

				{!value && !isDisabled && (
					<Button
						onClick={() => {
							setIsChanged(true);

							const valueName =
								type === 'resultOutputItem'
									? globalSettings.enums.successRedirectUrlKeys.variation
									: `${type}-${getUnique()}`;
							setAttributes({ [attribute]: valueName });
						}}
					>
						{type === 'resultOutputItem' ? __('Set name', 'eightshift-forms') : __('Generate name', 'eightshift-forms')}
					</Button>
				)}
			</div>
		);
	};

	let helpText = sprintf(
		__('Identifies the %s within form submission data. Must be unique. %s', 'eightshift-forms'),
		type,
		help,
	);

	if (type === 'resultOutputItem') {
		helpText = __(
			'Identifies the what result output item the user will see after successful submit redirect.',
			'eightshift-forms',
		);
	}

	return (
		<>
			{show && (
				<>
					<InputField
						label={<NameFieldLabel />}
						placeholder={__('Enter name', 'eightshift-forms')}
						help={helpText}
						value={value}
						onChange={(value) => {
							setIsChanged(true);
							setAttributes({ [attribute]: value });
						}}
						disabled={isDisabled}
					/>

					<NameChangeWarning
						isChanged={isChanged}
						type={type}
					/>
				</>
			)}
		</>
	);
};

/**
 * Toggle save on missing prop.
 *
 * @param {string} blockClientId Block client Id.
 * @param {string} key Manifest key to check.
 * @param {string} value Value to check.
 *
 * @returns void
 */
export const preventSaveOnMissingProps = (blockClientId, key, value) => {
	useEffect(() => {
		// Allows triggering this action only when the block is inserted in the editor.
		if (select('core/block-editor').getBlock(blockClientId)) {
			// Lock/unlock depending on the value.
			value === '' ? lockPostEditing(blockClientId, key) : unlockPostEditing(blockClientId, key);
		}

		// Use this method to detect if the block has been deleted from the block editor.
		return () => {
			unlockPostEditing(blockClientId, key);
		};
	}, [key, value, blockClientId]);
};

/**
 * Show warning if name value is changed.
 *
 * @param {bool} isChanged Is name changed.
 * @param {string} type Is this value.
 *
 * @returns Component
 */
export const NameChangeWarning = ({ isChanged = false, type = 'default' }) => {
	let text = '';

	if (!isChanged) {
		return null;
	}

	switch (type) {
		case 'value':
			text = __(
				'After changing the field value, ensure that you review all conditional tags and form multi-flow configurations to avoid any errors.',
				'eightshift-forms',
			);
			break;
		case 'step':
			text = __(
				'After changing the step name, ensure that you review forms multi-flow configurations to avoid any errors.',
				'eightshift-forms',
			);
			break;
		case 'resultOutputItem':
			text = __(
				'After changing the result item variable name, ensure that you provide the correct variation name via form settings.',
				'eightshift-forms',
			);
			break;
		default:
			text = __(
				'After changing the field name, ensure that you review all conditional tags and form multi-flow configurations to avoid any errors.',
				'eightshift-forms',
			);
			break;
	}

	return (
		<Notice
			label={text}
			type={'warning'}
		/>
	);
};

/**
 * Returns setting button component.
 *
 * @returns Component
 */
export const FormEditButton = ({ formId }) => {
	const wpAdminUrl = esFormsLocalization.wpAdminUrl;

	const { editFormUrl } = select(STORE_NAME).getSettings();

	return (
		<Button
			href={`${wpAdminUrl}${editFormUrl}&post=${formId}`}
			icon={icons.edit}
		>
			{__('Edit fields', 'eightshift-forms')}
		</Button>
	);
};

/**
 * Returns setting button component.
 *
 * @returns Component
 */
export const SettingsButton = ({ formId = null }) => {
	const wpAdminUrl = esFormsLocalization.wpAdminUrl;
	const postId = select('core/editor').getCurrentPostId();

	const id = formId ?? postId;

	const { settingsPageUrl } = select(STORE_NAME).getSettings();

	return (
		<Button
			onPress={() => {
				window.open(`${wpAdminUrl}${settingsPageUrl}&formId=${id}`, '_blank');
			}}
			icon={icons.options}
		>
			{__('Edit settings', 'eightshift-forms')}
		</Button>
	);
};

export const GlobalSettingsButton = () => {
	const wpAdminUrl = esFormsLocalization.wpAdminUrl;

	const { globalSettingsPageUrl } = select(STORE_NAME).getSettings();

	return (
		<Button
			onPress={() => {
				window.open(`${wpAdminUrl}${globalSettingsPageUrl}`, '_blank');
			}}
			icon={icons.globe}
		>
			{__('Edit global settings', 'eightshift-forms')}
		</Button>
	);
};

/**
 * Returns location button component.
 *
 * @returns Component
 */
export const LocationsButton = ({ formId = null }) => {
	const wpAdminUrl = esFormsLocalization.wpAdminUrl;
	const postId = select('core/editor').getCurrentPostId();

	const id = formId ?? postId;

	const { locationsPageUrl } = select(STORE_NAME).getSettings();

	return (
		<Button
			href={`${wpAdminUrl}${locationsPageUrl}&formId=${id}`}
			icon={icons.notebook}
		>
			{__('Locations', 'eightshift-forms')}
		</Button>
	);
};

/**
 * Returns dashboard button component.
 *
 * @returns Component
 */
export const DashboardButton = () => {
	const wpAdminUrl = esFormsLocalization.wpAdminUrl;

	const { dashboardPageUrl } = select(STORE_NAME).getSettings();

	return (
		<Button
			onPress={() => {
				window.open(`${wpAdminUrl}${dashboardPageUrl}`, '_blank');
			}}
			icon={icons.layoutAlt}
		>
			{__('Visit dashboard settings', 'eightshift-forms')}
		</Button>
	);
};

/**
 * Returns output select item with icon.
 *
 * @returns object
 */
export const outputFormSelectItemWithIcon = (props) => {
	const { label, id, metadata } = props;

	if (!id) {
		return '';
	}

	let outputLabel = unescapeHTML(label);
	let icon = getUtilsIcons('post');

	if (!outputLabel) {
		outputLabel = __(`Form ${id}`, 'eightshift-forms');
	}

	if (getUtilsIcons(metadata)) {
		icon = getUtilsIcons(metadata);
	}

	if (isDeveloperMode()) {
		outputLabel = `${outputLabel} (${id})`;
	}

	return {
		id,
		label: (
			<span
				dangerouslySetInnerHTML={{
					__html: `<span>${icon}</span>${outputLabel}`,
				}}
			/>
		),
		value: id,
		metadata,
	};
};
