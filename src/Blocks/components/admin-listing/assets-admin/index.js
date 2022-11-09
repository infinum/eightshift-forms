/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';
import manifest from './../manifest.json';

domReady(() => {
	if (typeof esFormsLocalization === 'undefined') {
		throw 'Your project is missing the global "esFormsLocalization" variable called from the enqueue script.';
	}

	const {
		componentJsFilterClass,
		componentJsItemClass,
	} = manifest;

	const selector = `.${componentJsFilterClass}`;
	const elements = document.querySelector(selector);

	if (elements) {
		import('./filter').then(({ Filter }) => {
			const filter = new Filter({
				filterSelector: selector,
				itemSelector: `.${componentJsItemClass}`,
			});

			filter.init();
		});
	}

});
