import manifest from './manifest.json';
import globalSettings from './../../manifest.json';

export const overrides = {
	...manifest,
	icon: {
		src: globalSettings.icons.submit,
	},
	parent: globalSettings.allowedBlocksList.integrationsNoBuilder,
};
