/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';

domReady(() => {
	const selector = '.js-es-block-form';
	const elements = document.querySelectorAll(selector);

	if (elements.length) {
		import('./form').then(({ Form }) => {
			const form = new Form({
				formSelector: selector,
				formSubmitRestApiUrl: esFormsLocalization.formSubmitRestApiUrl,
			});

			form.init();
		});
	}
});
