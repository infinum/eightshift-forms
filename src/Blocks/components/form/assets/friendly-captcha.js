/* global frcaptcha */

import { prefix, setStateWindow } from './state-init';

/**
 * FriendlyCaptcha class.
 */
export class FriendlyCaptcha {
	constructor(utils) {
		/** @type {import('./utils').Utils} */
		this.utils = utils;
		/** @type {import('./state').State} */
		this.state = this.utils.getState();

		this.widget = null;

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
		if (!this.state.getStateCaptchaIsUsed()) {
			return;
		}

		if (!this.state.getStateCaptchaLoadOnInit() && !document.querySelectorAll(this.state.getStateSelector('form', true))?.length) {
			return;
		}

		this.initWidget();
		this.initResetOnSubmit();
	}

	/**
	 * Reset widget after each form submission so a fresh token is ready for the next attempt.
	 *
	 * @returns {void}
	 */
	initResetOnSubmit() {
		window.addEventListener(this.state.getStateEvent('afterFormSubmit'), () => {
			this.widget?.reset();
		});
	}

	/**
	 * Initialize Friendly Captcha widget.
	 *
	 * @returns {void}
	 */
	initWidget() {
		if (typeof frcaptcha === 'undefined') {
			return;
		}

		const siteKey = this.state.getStateCaptchaSiteKey();

		// Create a hidden container for the widget.
		const container = document.createElement('div');
		container.style.display = 'none';
		document.body.appendChild(container);

		this.widget = frcaptcha.createWidget({
			element: container,
			sitekey: siteKey,
			startMode: 'auto',
			apiEndpoint: this.state.getStateCaptchaEndpoint(),
		});
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

		if (window[prefix].friendlyCaptcha) {
			return;
		}

		window[prefix].friendlyCaptcha = {
			init: () => {
				this.init();
			},
			initWidget: () => {
				this.initWidget();
			},
			initResetOnSubmit: () => {
				this.initResetOnSubmit();
			},
			getResponse: () => this.widget?.getResponse() ?? '',
			reset: () => {
				this.widget?.reset();
			},
		};
	}
}
