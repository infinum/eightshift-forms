/* global esFormsLocalization */
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
import { registerBlocks } from '@eightshift/frontend-libs-tailwind/scripts/editor';
import globalManifest from '../../manifest.json';
import './store';

registerBlocks(
	globalManifest,
	null,
	{},
	require.context('./../../components', true, /manifest.json$/),
	require.context('./../../custom', true, /manifest.json$/),
	require.context('./../../custom', true, /-block.js$/),
	require.context('./../../custom', true, /-hooks.js$/),
	require.context('./../../custom', true, /-transforms.js$/),
	require.context('./../../custom', true, /-deprecations.js$/),
	require.context('./../../custom', true, /-overrides.js$/),
);

if (esFormsLocalization?.currentPostType?.isForms) {
	globalManifest?.unregisterBlocks?.forms?.forEach((block) => unregisterBlockType(block));
}

if (esFormsLocalization?.currentPostType?.isResults) {
	globalManifest?.unregisterBlocks?.results?.forEach((block) => unregisterBlockType(block));
}

if (esFormsLocalization?.currentPostType?.isCommon) {
	globalManifest?.unregisterBlocks?.common?.forEach((block) => unregisterBlockType(block));
}
