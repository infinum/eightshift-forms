/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';
import { componentJsClass, componentCacheJsClass } from './../manifest.json';

domReady(() => {
	const selector = `.${componentJsClass}`;
	const elements = document.querySelectorAll(selector);

	if (elements.length) {
		import('./../assets/form').then(({ Form }) => {
			const form = new Form({
				formSelector: selector,
				formSubmitRestApiUrl: esFormsLocalization.formSettingsSubmitRestApiUrl,
				formIsAdmin: true,
			});

			form.init();
		});
	}

	const selectorCache = `.${componentCacheJsClass}`;
	const elementsCache = document.querySelectorAll(selectorCache);

	if (elementsCache.length) {
		import('./cache').then(({ Cache }) => {
			const cache = new Cache({
				selector: selectorCache,
				formSelector: selector,
				clearCacheRestUrl: esFormsLocalization.clearCacheRestUrl,
			});

			cache.init();
		});
	}
});
