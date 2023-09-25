/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';
import { setStateInitial } from './state/init';
import { Utils } from "../assets/utilities";
import { State } from './state';

// Global variable must be set for everything to work.
if (typeof esFormsLocalization === 'undefined') {
	throw Error('Your project is missing global variable "esFormsLocalization" called from the enqueue script in the forms.');
}

// Set initial state.
setStateInitial();

// Load state helpers.
const state = new State();

domReady(() => {
	// Load captcha if using initial.
	if (state.getStateCaptchaIsUsed()) {
		import('./captcha').then(({ Captcha }) => {
			new Captcha().init();
		});
	}

	if (!state.getStateSettingsFormDisableAutoInit()) {
		if (document.querySelectorAll(state.getStateSelectorsForm())?.length) {
			import('./form').then(({ Form }) => {
				new Form().init();
			});
		}
	} else {
		import('./form').then(({ Form }) => {
			new Form();

			new Utils().dispatchFormEvent(window, state.getStateEventsFormManualInitLoaded());
		});
	}
});
