/* global esFormsLocalization */

import { useEffect } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { select, dispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { createBlock, createBlocksFromInnerBlocksTemplate } from '@wordpress/blocks';
import { codeVariable, conditionalVisibility, edit, hide, layoutAlt, lightBulb, loader, magic, none, options, readOnly, requiredAlt, tagAlt, warning, warningFill, wrench } from '@eightshift/ui-components/icons';
import { lockPostEditing, unlockPostEditing, getUnique } from '@eightshift/frontend-libs-tailwind/scripts';
import { RichLabel, Button, InputField, Container, ContainerGroup, DecorativeTooltip } from '@eightshift/ui-components';
import { camelCase, clsx, upperFirst } from '@eightshift/ui-components/utilities';
import { FORMS_STORE_NAME } from './../../assets/scripts/store';
import { getRestUrl, getRestUrlByType } from '../form/assets/state-init';
import { HelpTooltip } from '../../assets/scripts/help-tooltip';
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
		<DecorativeTooltip text={!isOptional ? __('Name not set!', 'eightshift-forms') : __('If you are using conditional tags you must set name on this field.', 'eightshift-forms')}>
			<span className='esf:text-red-600'>{warningFill}</span>
		</DecorativeTooltip>
	);
};

export const StatusFieldOutput = ({ components }) => {
	if (!components?.length) {
		return null;
	}

	const statusIcons = {
		conditionals: conditionalVisibility,
		hidden: hide,
		missingName: warning,
		required: requiredAlt,
		disabled: none,
		readonly: readOnly,
	};

	const statusLabels = {
		conditionals: __('Conditional visibility rules are set', 'eightshift-forms'),
		hidden: __('Field is hidden', 'eightshift-forms'),
		missingName: __('Name not set!', 'eightshift-forms'),
		required: __('Field is required', 'eightshift-forms'),
		disabled: __('Field is disabled', 'eightshift-forms'),
		readonly: __('Field is read-only', 'eightshift-forms'),
	};

	return (
		<div className='esf:flex esf:gap-4 esf:justify-end esf:ml-auto'>
			{components.map((name) => {
				const icon = statusIcons[name];

				if (!icon) {
					return null;
				}

				const classes = clsx('esf:rounded-full esf:p-5', name === 'missingName' ? 'esf:bg-orange-600/5 esf:text-orange-600' : 'esf:bg-current/5 esf:text-current');

				return (
					<DecorativeTooltip
						text={statusLabels[name] || upperFirst(name)}
						key={name}
					>
						<div className={classes}>{icon}</div>
					</DecorativeTooltip>
				);
			})}
		</div>
	);
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
export const NameField = ({ value, attribute, help = '', disabledOptions = [], label: rawLabel, setAttributes, show = true, type, isChanged = false, isOptional = false, setIsChanged }) => {
	let label = rawLabel || __('Name', 'eightshift-forms');
	let icon = tagAlt;

	const isDisabled = isOptionDisabled(attribute, disabledOptions);

	let helpText = sprintf(__('Identifies the %s within form submission data. Must be unique. %s', 'eightshift-forms'), type, help);

	if (type === 'resultOutputItem') {
		icon = codeVariable;
		label = __('Variable name', 'eightshift-forms');
		helpText = __('Identifies the what result output item the user will see after successful form submission', 'eightshift-forms');
	}

	if (!show) {
		return null;
	}

	return (
		<ContainerGroup>
			<Container>
				<InputField
					icon={icon}
					label={label}
					placeholder={!value && !isOptional && __('Required', 'eightshift-forms')}
					actions={
						<>
							{!value && !isDisabled && type !== 'resultOutputItem' && (
								<Button
									onClick={() => {
										setIsChanged(false);

										const valueName = type === 'resultOutputItem' ? globalSettings.enums.successRedirectUrlKeys.variation : `${type}-${getUnique()}`;
										setAttributes({ [attribute]: valueName });
									}}
									icon={magic}
									size='small'
									type='selectedGhost'
									className='esf:h-24!'
								>
									{type === 'resultOutputItem' ? __('Set', 'eightshift-forms') : __('Generate', 'eightshift-forms')}
								</Button>
							)}

							<HelpTooltip hidden={!value && !isDisabled}>
								{helpText}

								{isOptional && type !== 'resultOutputItem' && __('Name field is required only if you are using conditional tags on this field.', 'eightshift-forms')}
							</HelpTooltip>
						</>
					}
					value={value}
					onChange={(value) => {
						setIsChanged(true);
						setAttributes({ [attribute]: value });
					}}
					disabled={isDisabled}
					monospaceFont={type === 'resultOutputItem'}
				/>
			</Container>

			<Container
				hidden={isOptional || value}
				className='es-uic-theme-orange'
				elevated
				centered
				accent
			>
				<RichLabel
					icon={warning}
					label={__('Form may not work correctly!', 'eightshift-forms')}
				/>
			</Container>

			{value && (
				<NameChangeWarning
					isChanged={isChanged}
					type={type}
				/>
			)}
		</ContainerGroup>
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
export const usePreventSaveOnMissingProps = (blockClientId, key, value) => {
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
			text = __('Review conditional tags and form multi-flow configurations to avoid errors', 'eightshift-forms');
			break;
		case 'step':
			text = __('Review multi-flow configurations to avoid errors', 'eightshift-forms');
			break;
		case 'resultOutputItem':
			text = __('Check that the correct variation name is provided in form settings', 'eightshift-forms');
			break;
		default:
			text = __('Review conditional tags and form multi-flow configurations to avoid errors.', 'eightshift-forms');
			break;
	}

	return (
		<Container
			className='es-uic-theme-blue'
			elevated
			centered
			accent
		>
			<RichLabel
				icon={lightBulb}
				label={text}
			/>
		</Container>
	);
};

/**
 * Returns setting button component.
 *
 * @returns Component
 */
export const FormEditButton = ({ formId }) => {
	const wpAdminUrl = esFormsLocalization.wpAdminUrl;

	return (
		<Button
			onPress={() => window.open(`${wpAdminUrl}${globalSettings.editFormUrl}&post=${formId}`, '_blank')}
			icon={edit}
			className='esf:grow'
			size='large'
		>
			{__('Edit', 'eightshift-forms')}
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

	return (
		<Button
			onPress={() => window.open(`${wpAdminUrl}${globalSettings.settingsPageUrl}&formId=${id}`, '_blank')}
			icon={options}
			className='esf:grow'
			size='large'
		>
			{__('Settings', 'eightshift-forms')}
		</Button>
	);
};

export const GlobalSettingsButton = () => {
	const wpAdminUrl = esFormsLocalization.wpAdminUrl;

	return (
		<Button
			onPress={() => window.open(`${wpAdminUrl}${globalSettings.globalSettingsPageUrl}`, '_blank')}
			icon={wrench}
			className='esf:grow'
			size='large'
		>
			{__('Plugin settings', 'eightshift-forms')}
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

	return (
		<Button
			onPress={() => window.open(`${wpAdminUrl}${globalSettings.locationsPageUrl}&formId=${id}`, '_blank')}
			icon={loader}
			className='esf:grow'
			size='large'
		>
			{__('Places used', 'eightshift-forms')}
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

	return (
		<Button
			onPress={() => window.open(`${wpAdminUrl}${globalSettings.dashboardPageUrl}`, '_blank')}
			icon={layoutAlt}
			className='esf:grow'
			size='large'
		>
			{__('Open Dashboard', 'eightshift-forms')}
		</Button>
	);
};
