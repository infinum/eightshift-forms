// eslint-disable-next-line no-unused-vars
/* global esFormsLocalization */

import globalManifest from '../../manifest.json';
import manifest from './manifest.json';

const forms = [
	...manifest.forms,
	...esFormsLocalization.formsSelectorTemplates,
];

const outputForms = [];

forms.forEach((form) => {
	const blockName = form.blockName.replace(`${globalManifest.namespace}/`, '');

	if (!esFormsLocalization.use.activeIntegrations.includes(blockName)) {
		return;
	}

	outputForms.push(form);
});

export const overrides = {
	...manifest,
	forms: outputForms,
};
