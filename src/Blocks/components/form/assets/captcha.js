/* global grecaptcha */

import { Utils } from "./utilities";

/**
 * Captcha class.
 */
export class Captcha {
	constructor(options = {}) {
		/** @type Utils */
		this.utils = options.utils ?? new Utils();
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

		// Load captcha on init.
		this.initCaptchaOnLoad();

		// Hide badge.
		this.initHideCaptchaBadge();
	}

	/**
	 * Initi captcha on load.
	 *
	 * @returns void
	 */
	initCaptchaOnLoad() {
		if (!this.utils.isCaptchaUsed() || !this.utils.isCaptchaInitUsed()) {
			return;
		}

		const actionName = this.utils.SETTINGS.CAPTCHA['initAction'];
		const siteKey = this.utils.SETTINGS.CAPTCHA['siteKey'];

		if (typeof grecaptcha === 'undefined') {
			return;
		}

		if (this.utils.isCaptchaEnterprise()) {
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
	formSubmitCaptchaInvisible(token, payed, action) {
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

		fetch(`${this.utils.formSubmitRestApiUrl}-captcha`, body)
		.then((response) => {
			return response.json();
		})
		.then((response) => {
			this.utils.dispatchFormEvent(window, this.utils.EVENTS.AFTER_CAPTCHA_INIT, response?.data?.response);
		});
	}

	/**
	 * Hide captcha badge.
	 *
	 * @public
	 */
	initHideCaptchaBadge() {
		if (!this.utils.isCaptchaUsed() || !this.utils.isCaptchaHideBadgeUsed()) {
			return;
		}

		document.querySelector('body').setAttribute(this.utils.DATA_ATTRIBUTES.hideCaptchaBadge, this.utils.isCaptchaHideBadgeUsed());
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
		if (typeof window?.[this.utils.getPrefix()]?.captcha === 'undefined') {
			window[this.utils.getPrefix()].captcha = {
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