import manifest from './manifest.json';
import globalSettings from './../../manifest.json';
import { getUtilsIcons } from '../../components/form/assets/state-init';

export const overrides = {
	...manifest,
	icon: {
		src: getUtilsIcons('submit') ?? manifest.icon.src,
	},
	parent: globalSettings.allowedBlocksList.integrationsNoBuilder,
};
