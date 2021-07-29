import domReady from '@wordpress/dom-ready';
import { componentJsClass } from './../manifest.json';

domReady(() => {
	const selector = `.${componentJsClass}`;
	const elements = document.querySelectorAll(selector);

	if (elements.length) {
		import('./regenerate-data-transients').then(({ RegenerateDataTransients }) => {
			const transients = new RegenerateDataTransients({
				selector,
				elements,
				nonceId: 'cache_nonce',
			});

			transients.initAll();
		});
	}
});
