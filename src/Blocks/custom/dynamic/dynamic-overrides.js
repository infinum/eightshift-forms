import manifest from './manifest.json';
import { getUtilsIcons } from '../../components/utils';

export const overrides = {
	...manifest,
	icon: {
		src: getUtilsIcons('dynamic') ?? manifest.icon.src,
	},
};
