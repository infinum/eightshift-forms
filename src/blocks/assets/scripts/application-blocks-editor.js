import { registerBlocks, registerVariations, outputCssVariablesGlobal } from '@eightshift/frontend-libs/scripts/editor';
import { Wrapper } from '../../wrapper/wrapper';
import WrapperManifest from '../../wrapper/manifest.json';
import globalSettings from '../../manifest.json';
import { hooks } from '../../wrapper/wrapper-hooks';

registerBlocks(
	globalSettings,
	Wrapper,
	WrapperManifest,
	require.context('./../../components', true, /manifest.json$/),
	require.context('./../../custom', true, /manifest.json$/),
	require.context('./../../custom', true, /-block.js$/),
	require.context('./../../custom', true, /-hooks.js$/),
	require.context('./../../custom', true, /-transforms.js$/),
);

registerVariations(
	globalSettings,
	require.context('./../../variations', true, /manifest.json$/),
);

// Run Wrapper hooks.
hooks();

// Output global css variables.
outputCssVariablesGlobal(globalSettings);