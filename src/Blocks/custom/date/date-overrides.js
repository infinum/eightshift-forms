import manifest from './manifest.json';
import globalSettings from './../../manifest.json';

export const overrides = {
	...manifest,
	icon: {
		src: globalSettings.icons.date,
	},
	parent: globalSettings.allowedBlocksList.integrationsNoBuilder,
};
