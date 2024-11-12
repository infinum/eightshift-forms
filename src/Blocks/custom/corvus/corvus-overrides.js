import globalManifest from '../../manifest.json';
import manifest from './manifest.json';
import { getUtilsIcons } from '../../components/form/assets/state-init';

export const overrides = {
	...manifest,
	icon:{
		src: getUtilsIcons('corvus') ?? manifest.icon.src,
	},
	attributes: {
		...manifest.attributes,
		corvusAllowedBlocks: {
			...manifest.attributes.corvusAllowedBlocks,
			default: globalManifest.allowedBlocksBuilderBlocksList
		},
	},
};
