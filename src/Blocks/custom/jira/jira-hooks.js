/* global esFormsLocalization */

import { addFilter } from '@wordpress/hooks';
import { select } from '@wordpress/data';
import { STORE_NAME } from '@eightshift/frontend-libs/scripts/editor';
import { isArray } from 'lodash';
import manifest from './manifest.json';

// Provide additional blocks to the forms.
export const hooks = () => {

	const namespace = select(STORE_NAME).getSettingsNamespace();

	const {
		blockName,
	} = manifest;

	// All adding additional blocks to the custom form builder.
	addFilter('blocks.registerBlockType', `${namespace}/${blockName}`, (settings, name) => {
		if (name === `${namespace}/${blockName}`) {
			if (typeof esFormsLocalization !== 'undefined' && isArray(esFormsLocalization?.additionalBlocks)) {
				esFormsLocalization.additionalBlocks.forEach((element) => {
					if (!settings.attributes.jiraAllowedBlocks.default.includes(element)) {
						settings.attributes.jiraAllowedBlocks.default.push(element);
					}
				});
			}

			select(STORE_NAME).getSettings().fieldsAlways.forEach((element) => {
				if (!settings.attributes.jiraAllowedBlocks.default.includes(element)) {
					settings.attributes.jiraAllowedBlocks.default.push(element);
				}
			});
		}

		return settings;
	});
};
