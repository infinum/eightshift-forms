/* global esFormsLocalization */

import { addFilter } from '@wordpress/hooks';
import { select } from '@wordpress/data';
import { STORE_NAME } from '@eightshift/frontend-libs/scripts/editor';
import { isArray } from 'lodash';

// Provide additional blocks to the forms.
export const hooks = () => {
	const { blockName } = select(STORE_NAME).getBlock('calculator');
	const namespace = select(STORE_NAME).getSettingsNamespace();

	// Adding all additional blocks to the custom form builder. 
	addFilter('blocks.registerBlockType', `${namespace}/${blockName}`, (settings, name) => {
		if (name === `${namespace}/${blockName}`) {
			if (typeof esFormsLocalization !== 'undefined' && isArray(esFormsLocalization?.additionalBlocks)) {
				esFormsLocalization.additionalBlocks.forEach((element) => {
					if (!settings.attributes.calculatorAllowedBlocks.default.includes(element)) {
						settings.attributes.calculatorAllowedBlocks.default.push(element);
					}
				});
			}

			select(STORE_NAME).getSettings().allowedBlocksBuilderIntegrationAdditionalBlocksList.forEach((element) => {
				if (!settings.attributes.calculatorAllowedBlocks.default.includes(element)) {
					settings.attributes.calculatorAllowedBlocks.default.push(element);
				}
			});
		}

		return settings;
	});
};

