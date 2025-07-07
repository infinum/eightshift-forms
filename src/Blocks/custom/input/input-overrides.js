import manifest from './manifest.json';
import globalSettings from './../../manifest.json';

export const overrides = {
	...manifest,
	icon: {
		src: globalSettings.icons.input,
	},
	parent: globalSettings.allowedBlocksList.integrationsNoBuilder,
};
