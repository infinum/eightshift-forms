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
		if (!this.state.getStateFriendlyCaptchaIsUsed()) {
			return;
		}

		this.initWidget();
	}

	/**
	 * Initialize Friendly Captcha widget.
	 *
	 * @returns {void}
	 */
	initWidget() {
		const siteKey = this.state.getStateFriendlyCaptchaSiteKey();

		if (typeof frcaptcha === 'undefined') {
			return;
		}

		// Create a hidden container for the widget.
		const container = document.createElement('div');
		container.style.display = 'none';
		document.body.appendChild(container);

		this.widget = frcaptcha.createWidget({
			element: container,
			sitekey: siteKey,
			startMode: 'auto',
			apiEndpoint: this.state.getStateFriendlyCaptchaEndpoint(),
		});
	}

	/**
	 * Get the current response token from the widget.
	 *
	 * @returns {string} The solution token.
	 */
	getResponse() {
		return this.widget?.getResponse() ?? '';
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
			getResponse: () => {
				return this.getResponse();
			},
		};
	}
}
