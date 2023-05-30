/* global grecaptcha */

import { Data } from "./data";
import { State } from "./state";
import { Utils } from "./utilities";

/**
 * Captcha class.
 */
export class Captcha {
	constructor(options = {}) {
		this.data = new Data(options);
		this.state = new State(options);
		this.utils = new Utils(options);
	}

	////////////////////////////////////////////////////////////////
	// Public methods
	////////////////////////////////////////////////////////////////

	/**
	 * Init all actions.
	 * 
	 * @public
	 */
	init() {
		// Set all public methods.
		this.publicMethods();

		// // Load captcha on init.
		this.initCaptchaOnLoad();

		// // Hide badge.
		this.initHideCaptchaBadge();
	}

	/**
	 * Initi captcha on load.
	 *
	 * @returns void
	 */
	initCaptchaOnLoad() {
		if (!this.state.getStateCaptchaIsUsed() || !this.state.getStateCaptchaLoadOnInit()) {
			return;
		}

		const actionName = this.state.getStateCaptchaInitAction();
		const siteKey = this.state.getStateCaptchaSiteKey();

		if (typeof grecaptcha === 'undefined') {
			return;
		}

		if (this.state.getStateCaptchaIsEnterprise()) {
			grecaptcha.enterprise.ready(async () => {
				await grecaptcha.enterprise.execute(siteKey, {action: actionName}).then((token) => {
					this.formSubmitCaptchaInvisible(token, 'enterprise', actionName);
				});
			});
		} else {
			grecaptcha.ready(async () => {
				await grecaptcha.execute(siteKey, {action: actionName}).then((token) => {
					this.formSubmitCaptchaInvisible(token, 'free', actionName);
				});
			});
		}
	}

	/**
	 *  Handle form submit and all logic in case we have captcha in place for init load.
	 * 
	 * @param {string} token Captcha token from api.
	 *
	 * @public
	 */
	formSubmitCaptchaInvisible(formId, token, payed, action) {
		// Populate body data.
		const body = {
			method: 'POST',
			mode: 'same-origin',
			headers: {
				Accept: 'application/json',
			},
			body: JSON.stringify({
				token,
				payed,
				action,
			}),
			credentials: 'same-origin',
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		fetch(this.state.getStateCaptchaSubmitUrl(), body)
		.then((response) => {
			return response.json();
		})
		.then((response) => {
			this.utils.dispatchFormEvent(window, this.data.EVENTS.AFTER_CAPTCHA_INIT, response?.data?.response);
		});
	}

	/**
	 * Hide captcha badge.
	 *
	 * @public
	 */
	initHideCaptchaBadge() {
		if (!this.state.getStateCaptchaIsUsed()) {
			return;
		}

		document.querySelector('body').setAttribute(this.data.DATA_ATTRIBUTES.hideCaptchaBadge, this.state.getStateCaptchaHideBadge());
	}

	////////////////////////////////////////////////////////////////
	// Private methods - not shared to the public window object.
	////////////////////////////////////////////////////////////////

	/**
	 * Set all public methods.
	 * 
	 * @private
	 */
	publicMethods() {
		if (typeof window?.[this.data.prefix]?.captcha === 'undefined') {
			window[this.data.prefix].captcha = {
				init: () => {
					this.init();
				},
				initCaptchaOnLoad: () => {
					this.initCaptchaOnLoad();
				},
				formSubmitCaptchaInvisible: (token, payed, action) => {
					this.formSubmitCaptchaInvisible(token, payed, action);
				},
				initHideCaptchaBadge: () => {
					this.initHideCaptchaBadge();
				}
			};
		}
	}
}
