import manifest from './manifest.json';
import { getUtilsIcons } from '../../components/utils';

export const overrides = {
	...manifest,
	icon: {
		src: getUtilsIcons('moments') ?? manifest.icon.src,
	},
};
