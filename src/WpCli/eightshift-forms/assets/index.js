/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';

export default function() {
	domReady(() => {
		const selector = `.js-es-block`;
		const elements = document.querySelectorAll(selector);

		if (elements.length && typeof esFormsLocalization !== 'undefined') {
			import('./form').then(({ Form }) => {
				const form = new Form({
					formSelector: selector,
				});

				form.init();
			});
		}
	});
}
