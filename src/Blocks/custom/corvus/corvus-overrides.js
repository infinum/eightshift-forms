import globalManifest from '../../manifest.json';
import manifest from './manifest.json';
import { getUtilsIcons } from '../../components/form/assets/state-init';

export const overrides = {
	...manifest,
	icon:{
		src: getUtilsIcons('corvus') ?? manifest.icon.src,
	},
	parent: globalManifest.allowedBlocksList.formsCpt,
};
