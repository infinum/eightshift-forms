import manifest from './manifest.json';
import globalSettings from './../../manifest.json';

export const overrides = {
	...manifest,
	icon: {
		src: globalSettings.icons.file,
	},
	parent: globalSettings.allowedBlocksList.integrationsNoBuilder,
};
