import manifest from './manifest.json';
import globalSettings from './../../manifest.json';

export const overrides = {
	...manifest,
	parent: [
		...globalSettings.allowedBlocksList.integrationsBuilder,
		...globalSettings.allowedBlocksList.integrationsNoBuilder,
	],
};
