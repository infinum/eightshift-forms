import globalManifest from './../../manifest.json';
import manifest from './manifest.json';

export const overrides = {
	...manifest,
	parent: globalManifest.allowedBlocksList.formsCpt,
};
