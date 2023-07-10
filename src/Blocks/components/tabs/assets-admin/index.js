/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';
import manifest from './../manifest.json';

domReady(() => {
	if (typeof esFormsLocalization === 'undefined') {
		throw 'Your project is missing the global "esFormsLocalization" variable called from the enqueue script.';
	}

	const selector = `.${manifest.componentJsClass}`;
	const elements = document.querySelectorAll(selector);

	if (elements.length) {
		import('./tabs').then(({ Tabs }) => {
			const tabs = new Tabs({
				tabsSelector: selector,
				tabSelector: `.${manifest.componentJsTabClass}`,
			});

			tabs.init();
		});
	}

});
