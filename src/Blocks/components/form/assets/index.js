/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';
import manifest from './../manifest.json';
import { Captcha } from './captcha';
import { Utils } from './utilities';

if (typeof esFormsLocalization === 'undefined') {
	console.warn('Your project is missing global variable esFormsLocalization called from the enqueue script in the forms. Forms will work but they will not get the admin settings configuration.');
}

// Run initial utils.
const utils = new Utils();

// Run cpatcha on init state.
const captcha = new Captcha();

/**
 * Init all functionality with one function.
 *
 * @public
 */
function initAll() {
	import('./form').then(({ Form }) => {
		const form = new Form({
			utils
		});

		// Run forms.
		form.init();
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

		captcha.init();
	});
} else {
	// Load initAll method in window object for manual trigger.
	window[utils.prefix] = {
		...window[utils.prefix],
		initAll: () => {
			initAll();
		},
	};
}
