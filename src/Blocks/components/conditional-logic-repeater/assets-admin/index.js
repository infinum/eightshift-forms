/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';
import manifest from '../manifest.json';

domReady(() => {
	if (typeof esFormsLocalization === 'undefined') {
		throw 'Your project is missing the global "esFormsLocalization" variable called from the enqueue script.';
	}

	// const selector = `.${componentJsClass}`;
	// const elements = document.querySelectorAll(selector);

	// if (elements.length) {
		import('./conditional-logic-repeater').then(({ conditionalLogicRepeater }) => {
			conditionalLogicRepeater();
		});
	// }

});
