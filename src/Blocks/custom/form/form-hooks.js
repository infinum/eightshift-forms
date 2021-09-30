/* global esFormsBlocksLocalization */
import { addFilter} from '@wordpress/hooks';
import manifest from './manifest.json';
import globalManifest from '../../manifest.json';

// Provide additional blocks to the forms.
export const hooks = () => {

	const {
		namespace,
	} = globalManifest;

	const {
		blockName,
	} = manifest;

	// All adding additional blocks to the custom form builder.
	addFilter('blocks.registerBlockType', `${namespace}/${blockName}`, (settings, name) => {
		if ( name === `${namespace}/${blockName}` && Array.isArray(esFormsBlocksLocalization.additionalBlocks) ) {
			esFormsBlocksLocalization.additionalBlocks.forEach((element) => {
				settings.attributes.formAllowedBlocks.default.push(element);
			});
		}

		return settings;
	});
};
