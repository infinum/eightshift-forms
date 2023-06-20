/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';
import { prefix, setStateInitial } from './state/init';
import { State } from './state';


if (typeof esFormsLocalization === 'undefined') {
	console.warn('Your project is missing global variable esFormsLocalization called from the enqueue script in the forms. Forms will work but they will not get the admin settings configuration.');
}

// Set initial state.
setStateInitial();

// Load state helpers.
const state = new State();

// Load captcha if using initial.
if (state.getStateCaptchaIsUsed()) {
	import('./captcha').then(({ Captcha }) => {
		new Captcha().init();
	});
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
const disableAutoInit = state.getStateSettingsFormDisableAutoInit();

// Load normal forms on dom ready event otherwise use manual trigger from the window object.
if (!disableAutoInit) {
	domReady(() => {
		const elements = state.getStateSelectorsForm();

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
