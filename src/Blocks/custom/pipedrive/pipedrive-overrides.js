import globalManifest from '../../manifest.json';
import manifest from './manifest.json';
import { getUtilsIcons } from '../../components/utils';

export const overrides = {
	...manifest,
	icon: {
		src: getUtilsIcons('pipedrive') ?? manifest.icon.src,
	},
	parent: globalManifest.allowedBlocksList.formsCpt,
};
