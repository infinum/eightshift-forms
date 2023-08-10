// eslint-disable-next-line no-unused-vars
/* global esFormsLocalization */

import manifest from './manifest.json';

export const overrides = {
	...manifest,
	forms: [
		...manifest.forms,
		...esFormsLocalization.formsSelectorTemplates,
	],
};
