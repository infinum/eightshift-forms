// eslint-disable-next-line no-unused-vars

import globalManifest from '../../manifest.json';
import manifest from './manifest.json';

export const overrides = {
	...manifest,
	attributes: {
		...manifest.attributes,
		calculatorAllowedBlocks: {
			...manifest.attributes.calculatorAllowedBlocks,
			default: globalManifest.allowedBlocksBuilderBlocksList
		},
	},
};
