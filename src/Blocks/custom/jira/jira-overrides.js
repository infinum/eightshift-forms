// eslint-disable-next-line no-unused-vars

import globalManifest from '../../manifest.json';
import manifest from './manifest.json';
import { getUtilsIcons } from '../../components/form/assets/state-init';

export const overrides = {
	...manifest,
	icon:{
		src: getUtilsIcons('jira') ?? manifest.icon.src,
	},
	attributes: {
		...manifest.attributes,
		jiraAllowedBlocks: {
			...manifest.attributes.jiraAllowedBlocks,
			default: globalManifest.allowedBlocksBuilderBlocksList
		},
	},
};
