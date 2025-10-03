/* global grecaptcha */

import { prefix, setStateWindow } from './state-init';

/**
 * Captcha class.
 */
export class Captcha {
	constructor(utils) {
		/** @type {import('./utils').Utils} */
		this.utils = utils;
		/** @type {import('./state').State} */
		this.state = this.utils.getState();

		// Set all public methods.
		this.publicMethods();
	}

	////////////////////////////////////////////////////////////////
	// Public methods
	////////////////////////////////////////////////////////////////

	/**
	 * Init all actions.
	 *
	 * @returns {void}
	 */
	init() {
		// Load captcha on init.
		this.initCaptchaOnLoad();

		// Hide badge.
		this.initHideCaptchaBadge();
	}

	/**
	 * Initialize captcha on load.
	 *
	 * @returns {void}
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
			grecaptcha?.enterprise?.ready(async () => {
				try {
					const token = await grecaptcha?.enterprise?.execute(siteKey, {action: actionName});

					this.formSubmitCaptchaInvisible(token, true, actionName);
				} catch (error) {
					throw new Error(`API response returned fatal error. Function used: "initCaptchaOnLoad". ${error}`);
				}
			});
		} else {
			grecaptcha?.ready(async () => {
				try {
					const token = await grecaptcha?.execute(siteKey, {action: actionName});

					this.formSubmitCaptchaInvisible(token, false, actionName);
				} catch (error) {
					throw new Error(`API response returned fatal error. Function used: "initCaptchaOnLoad". ${error}`);
				}
			});
		}
	}

	/**
	 * Handle form submit and all logic in case we have captcha in place for init load.
	 *
	 * @param {string} token Captcha token from api.
	 * @param {bool} isEnterprise Is enterprise setup.
	 * @param {string} action Action to use.
	 *
	 * @returns {void}
	 */
	async formSubmitCaptchaInvisible(token, isEnterprise, action) {
		const formData = new FormData();

		formData.append('token', token);
		formData.append('isEnterprise', isEnterprise);
		formData.append('action', action);

		// Populate body data.
		const body = {
			method: 'POST',
			mode: 'same-origin',
			headers: {
				Accept: 'application/json',
			},
			body: formData,
			redirect: 'follow',
			referrer: 'no-referrer',
		};

		try {
			const response = await fetch(this.state.getRestUrl('captcha'), body);
			const parsedResponse = await response.json();

			const parsedResponseData = parsedResponse?.data;

			if (parsedResponse?.status === 'error' && !parsedResponseData?.[this.state.getStateResponseOutputKey('captchaIsSpam')]) {
				throw new Error(`API response returned an error. Function used: "formSubmitCaptchaInvisible". Msg: ${response?.message} Action: ${action}`);
			}

			this.utils.dispatchFormEventWindow(this.state.getStateEvent('afterCaptchaInit'), { responseData: parsedResponse, rawResponse: response });
		} catch ({name, message}) {
			if (name === 'AbortError') {
				return;
			}

			throw new Error(this.utils.formSubmitResponseError(null, 'invisibleCaptcha', name, message));
		}
	}

	/**
	 * Hide captcha badge.
	 *
	 * @returns {void}
	 */
	initHideCaptchaBadge() {
		if (!this.state.getStateCaptchaIsUsed()) {
			return;
		}

		document?.body?.setAttribute(this.state.getStateAttribute('hideCaptchaBadge'), this.state.getStateCaptchaHideBadge());
	}

	////////////////////////////////////////////////////////////////
	// Private methods - not shared to the public window object.
	////////////////////////////////////////////////////////////////

	/**
	 * Set all public methods.
	 *
	 * @returns {void}
	 */
	publicMethods() {
		setStateWindow();

		if (window[prefix].captcha) {
			return;
		}

		window[prefix].captcha = {
			init: () => {
				this.init();
			},
			initCaptchaOnLoad: () => {
				this.initCaptchaOnLoad();
			},
			formSubmitCaptchaInvisible: (token, isEnterprise, action) => {
				this.formSubmitCaptchaInvisible(token, isEnterprise, action);
			},
			initHideCaptchaBadge: () => {
				this.initHideCaptchaBadge();
			},
		};
	}
}
