import globalManifest from '../../manifest.json';
import manifest from './manifest.json';
import { getUtilsIcons } from '../../components/utils';

export const overrides = {
	...manifest,
	icon: {
		src: getUtilsIcons('paycek') ?? manifest.icon.src,
	},
	attributes: {
		...manifest.attributes,
		paycekAllowedBlocks: {
			...manifest.attributes.paycekAllowedBlocks,
			default: globalManifest.allowedBlocksBuilderBlocksList,
		},
	},
};
