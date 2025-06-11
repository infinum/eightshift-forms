/**
 * This is the main entry point for Block Editor blocks used for the `WordPress admin editor`.
 * This file registers styles and scripts.
 *
 * Usage: `WordPress admin editor`.
 */

import { dynamicImport } from '@eightshift/frontend-libs-tailwind/scripts/helpers';
import { unregisterBlockType } from '@wordpress/blocks';
import { registerBlocks } from '@eightshift/frontend-libs-tailwind/scripts/editor';
import globalManifest from '../manifest.json';
import './scripts/store';

/* ------------------------------------------------------------ */
/* Images */
/* ------------------------------------------------------------ */

import './images/cover.jpg';

/* ------------------------------------------------------------ */
/* Styles */
/* ------------------------------------------------------------ */

import './styles/editor.css';

// dynamicImport(require.context('./../components', true, /-editor\.css$/));
// dynamicImport(require.context('./../custom', true, /-editor\.css$/));

/* ------------------------------------------------------------ */
/* Scripts */
/* ------------------------------------------------------------ */

registerBlocks(
	globalManifest,
	null,
	{},
	require.context('./../components', true, /manifest.json$/),
	require.context('./../custom', true, /manifest.json$/),
	require.context('./../custom', true, /-block.js$/),
	require.context('./../custom', true, /-hooks.js$/),
	require.context('./../custom', true, /-transforms.js$/),
	require.context('./../custom', true, /-deprecations.js$/),
	require.context('./../custom', true, /-overrides.js$/),
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
