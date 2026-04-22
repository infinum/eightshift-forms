/* global esFormsLocalization */

import domReady from '@wordpress/dom-ready';
import { StateEnum, setStateInitial } from './state-init';
import { Utils } from './utils';

// Global variable must be set for everything to work.
if (typeof esFormsLocalization === 'undefined') {
	throw Error('Your project is missing global variable "esFormsLocalization" called from the enqueue script in the forms.');
}

// Set initial state.
setStateInitial();

// Load state helpers.
const utils = new Utils();
const state = utils.getState();

domReady(() => {
	if (state.getStateCaptchaIsUsed()) {
		if (state.getStateCaptchaType() === StateEnum.CAPTCHA_TYPE_FRIENDLY) {
			// Friendly Captcha renders its widget inside the form, so only load
			// it on pages that actually contain one.
			if (document.querySelectorAll(state.getStateSelector('form', true))?.length) {
				import('./friendly-captcha').then(({ FriendlyCaptcha }) => {
					new FriendlyCaptcha(utils).init();
				});
			}
		} else {
			import('./captcha').then(({ Captcha }) => {
				new Captcha(utils).init();
			});
		}
	}

	if (!state.getStateSettingsFormDisableAutoInit()) {
		if (document.querySelectorAll(state.getStateSelector('form', true))?.length) {
			import('./form').then(({ Form }) => {
				new Form(utils).init();
			});
		}
	} else {
		import('./form').then(({ Form }) => {
			new Form(utils);

			utils.dispatchFormEventWindow(state.getStateEvent('formManualInitLoaded'));
		});
	}
});
