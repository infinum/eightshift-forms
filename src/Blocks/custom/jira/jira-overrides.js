// eslint-disable-next-line no-unused-vars
// global esRedesignBlocksLocalization

import globalManifest from '../../manifest.json';
import manifest from './manifest.json';

export const overrides = {
	...manifest,
	attributes: {
		...manifest.attributes,
		jiraAllowedBlocks: {
			...manifest.attributes.jiraAllowedBlocks,
			default: globalManifest.fields
		},
	},
};
