import { getUtilsIcons } from '../../components/utils';
import globalManifest from '../../manifest.json';
import manifest from './manifest.json';

export const overrides = {
	...manifest,
	icon: {
		src: getUtilsIcons('calculate') ?? manifest.icon.src,
	},
	attributes: {
		...manifest.attributes,
		calculatorAllowedBlocks: {
			...manifest.attributes.calculatorAllowedBlocks,
			default: globalManifest.allowedBlocksBuilderBlocksList,
		},
	},
};
