import manifest from './manifest.json';
import globalSettings from './../../manifest.json';

export const overrides = {
	...manifest,
	icon: {
		src: globalSettings.icons.textarea,
	},
	parent: globalSettings.allowedBlocksList.integrationsNoBuilder,
};
