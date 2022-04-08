/* global esFormsBlocksLocalization */
/**
 * This is the main entry point for Block Editor blocks scripts used for the `WordPress admin editor`.
 * This file registers blocks dynamically using `registerBlocks` helper method.
 * File names must follow the naming convention to be able to run dynamically.
 *
 * `src/blocks/custom/block_name/manifest.json`.
 * `src/blocks/custom/block_name/block_name.js`.
 *
 * Usage: `WordPress admin editor`.
 */

import { unregisterBlockType } from '@wordpress/blocks';
import { select } from '@wordpress/data';
import {
	registerBlocks,
	outputCssVariablesGlobal,
	STORE_NAME,
} from '@eightshift/frontend-libs/scripts/editor';
import { Wrapper } from '../../wrapper/wrapper';
import WrapperManifest from '../../wrapper/manifest.json';
import globalSettings from '../../manifest.json';

registerBlocks(
	globalSettings,
	Wrapper,
	WrapperManifest,
	require.context('./../../components', true, /manifest.json$/),
	require.context('./../../custom', true, /manifest.json$/),
	require.context('./../../custom', true, /-block.js$/),
	require.context('./../../custom', true, /-hooks.js$/),
	require.context('./../../custom', true, /-transforms.js$/),
	require.context('./../../custom', true, /-deprecations.js$/),
	require.context('./../../custom', true, /-overrides.js$/),
);

// Output global css variables.
outputCssVariablesGlobal();

// Remove form-selector block from anywhere else other than form CPT.
if (esFormsBlocksLocalization?.postType !== 'eightshift-forms') {
	const namespace = select(STORE_NAME).getSettingsNamespace();

	unregisterBlockType(`${namespace}/form-selector`);
}
