/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';
import manifest from './../manifest.json';

domReady(() => {
	const {
		componentJsClass,
		componentCacheJsClass,
	} = manifest;
	const selector = `.${componentJsClass}`;
	const elements = document.querySelectorAll(selector);

	if (elements.length && typeof esFormsLocalization !== 'undefined') {
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

	if (elementsCache.length && typeof esFormsLocalization !== 'undefined') {
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
