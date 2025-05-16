import manifest from './manifest.json';
import { getUtilsIcons } from '../../components/form/assets/state-init';
import globalSettings from './../../manifest.json';

export const overrides = {
	...manifest,
	icon:{
		src: getUtilsIcons('starRating') ?? manifest.icon.src,
	},
	parent: globalSettings.allowedBlocksList.integrationsNoBuilder,
};
