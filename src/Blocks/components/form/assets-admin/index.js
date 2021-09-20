/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';

domReady(() => {
	
	const selector = '.js-es-block-form';
	const elements = document.querySelectorAll(selector);
	console.log(elements);

	if (elements.length) {
		import('./../assets/form').then(({ Form }) => {
			const form = new Form({
				formSelector: selector,
				formSubmitRestApiUrl: esFormsLocalization.formSettingsSubmitRestApiUrl,
			});

			form.init();
		});
	}
});
