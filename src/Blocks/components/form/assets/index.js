/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';
import { componentJsClass } from './../manifest.json';

domReady(() => {
	const selector = `.${componentJsClass}`;
	const elements = document.querySelectorAll(selector);

	if (elements.length) {
		import('./form').then(({ Form }) => {
			const form = new Form({
				formSelector: selector,
				formSubmitRestApiUrl: esFormsLocalization.formSubmitRestApiUrl,
				redirectionTimeout: esFormsLocalization.redirectionTimeout,
				hideGlobalMessageTimeout: esFormsLocalization.hideGlobalMessageTimeout,
			});

			form.init();
		});
	}
});
