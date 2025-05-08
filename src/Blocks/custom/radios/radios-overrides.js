import manifest from './manifest.json';
import { getUtilsIcons } from '../../components/form/assets/state-init';

export const overrides = {
	...manifest,
	icon: {
		src: getUtilsIcons('radios') ?? manifest.icon.src,
	},
};
