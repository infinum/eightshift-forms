/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';
import manifest from './../manifest.json';
import { prefix } from './state/init';

if (typeof esFormsLocalization === 'undefined') {
	console.warn('Your project is missing global variable esFormsLocalization called from the enqueue script in the forms. Forms will work but they will not get the admin settings configuration.');
}

/**
 * Init all functionality with one function.
 *
 * @returns {void}
 */
function initAll() {
	import('./form').then(({ Form }) => {
		new Form().init();
	});
}

// You can disable auto init from the admin.
const disableAutoInit = Boolean(esFormsLocalization.formDisableAutoInit);

// Load normal forms on dom ready event otherwise use manual trigger from the window object.
if (!disableAutoInit) {
	domReady(() => {
		const {
			componentJsClass,
		} = manifest;

		const elements = document.querySelectorAll(`.${componentJsClass}`);

		if (elements.length) {
			initAll();
		}
	});
} else {
	// Load initAll method in window object for manual trigger.
	window[prefix] = {
		...window[prefix],
		initAll: () => {
			initAll();
			return 'Eightshift Forms initialized';
		},
	};
}
