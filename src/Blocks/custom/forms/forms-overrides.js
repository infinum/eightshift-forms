import manifest from './manifest.json';
import { getUtilsIcons } from '../../components/form/assets/state-init';

export const overrides = {
	...manifest,
	icon:{
		src: getUtilsIcons('form') ?? manifest.icon.src,
	}
};
