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

		// Map of widget instances: '__global' key for Smart/Zero-click (hidden) mode, form element for One-click mode.
		this.widgets = new Map();

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
		window.addEventListener(this.state.getStateEvent('afterFormSubmit'), (event) => {
			if (this.state.getStateCaptchaWidgetMode() !== 'one-click') {
				this.widgets.get('__global')?.reset();

				return;
			}

			const formId = event?.detail?.formId;

			if (!formId) {
				return;
			}

			const formElement = this.state.getStateFormElement(formId);
			this.widgets.get(formElement)?.reset();
		});
	}

	/**
	 * Initialize Friendly Captcha widget(s).
	 *
	 * In standard mode: one hidden global widget shared across all forms on the page.
	 * In one-click mode: one visible widget per form, inserted at the end of each form.
	 *
	 * @returns {void}
	 */
	initWidget() {
		if (typeof frcaptcha === 'undefined') {
			return;
		}

		const siteKey = this.state.getStateCaptchaSiteKey();
		const apiEndpoint = this.state.getStateCaptchaEndpoint();

		if (this.state.getStateCaptchaWidgetMode() !== 'one-click') {
			const container = document.createElement('div');
			container.style.display = 'none';
			document.body.appendChild(container);

			this.widgets.set('__global', frcaptcha.createWidget({
				element: container,
				sitekey: siteKey,
				startMode: 'auto',
				apiEndpoint,
			}));

			return;
		}

		const forms = document.querySelectorAll(this.state.getStateSelector('form', true));
		forms.forEach((form) => {
			const container = document.createElement('div');
			container.setAttribute('data-frc-widget', '');
			form.appendChild(container);

			this.widgets.set(form, frcaptcha.createWidget({
				element: container,
				sitekey: siteKey,
				startMode: 'focus',
				apiEndpoint,
			}));
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
			getResponse: (formElement = null) => {
				if (formElement && this.state.getStateCaptchaWidgetMode() === 'one-click') {
					return this.widgets.get(formElement)?.getResponse() ?? '';
				}

				return this.widgets.get('__global')?.getResponse() ?? '';
			},
			reset: (formElement = null) => {
				if (!formElement || this.state.getStateCaptchaWidgetMode() !== 'one-click') {
					this.widgets.get('__global')?.reset();

					return;
				}

				this.widgets.get(formElement)?.reset();
			},
		};
	}
}
