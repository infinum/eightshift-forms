// eslint-disable-next-line no-unused-vars
/* global esFormsLocalization */

import manifest from './manifest.json';
import { getUtilsIcons } from '../../components/form/assets/state-init';

export const overrides = {
	...manifest,
	icon:{
		src: getUtilsIcons('dynamic') ?? manifest.icon.src,
	}
};
